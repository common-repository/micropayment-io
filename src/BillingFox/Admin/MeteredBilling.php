<?php

class BillingFox_Admin_MeteredBilling extends BillingFox_ContainerAware implements BillingFox_RegistrationInterface
{
    const FIELD_SLUG = 'billingfox_slug';
    const FIELD_PRICE = 'billingfox_price';
    const FIELD_PREVIEW_ID = 'billingfox_preview_id';

    public function register()
    {
        add_action('add_meta_boxes', [$this, 'registerMetaBoxes']);
        add_action('save_post', [$this, 'saveMetaBox']);
        add_action('admin_footer', [$this, 'renderImageFooterJs']);
    }

    public function registerMetaBoxes()
    {
        /*
         * The following are not working properly:
         *  - attachment
         */
        $screens = ['post', 'page'];
        foreach ($screens as $screen) {
            add_meta_box(
                'billingfox_metered_billing_'.$screen,
                __('Metered Billing'),
                [$this, 'renderMeteredBillingBox'],
                $screen
            );
        }
    }

    public function saveMetaBox($post_id)
    {
        $keys = [self::FIELD_PRICE, self::FIELD_PREVIEW_ID, self::FIELD_SLUG];

        foreach ($keys as $key) {
            if (!array_key_exists($key, $_POST)) {
                continue;
            }

            update_post_meta($post_id, $key, $_POST[$key]);
        }
    }

    public function renderMeteredBillingBox($post)
    {
        wp_enqueue_media();

        $this->renderNumber($post, __('Credits', BILLING_FOX_TRANSLATE), self::FIELD_PRICE, __('1 Credit = 0.01 USD'));
        //$this->renderChoice($post, __('Credits', BILLING_FOX_TRANSLATE), 'billingfox_price', __('1 Credit = 0.01 USD'));

        $this->renderImage($post, __('Preview image', BILLING_FOX_TRANSLATE), self::FIELD_PREVIEW_ID);

        $this->renderText($post, __('Slug', BILLING_FOX_TRANSLATE), self::FIELD_SLUG, __('Same Keys will only count once, defaults to current page'));

        $this->renderInfo(sprintf(__('If the [%s] shortcode is used in the content then only those parts will be protected'), BillingFox_Post_ShortCode::SHORT_CODE));
    }

    public function renderImageFooterJs()
    {
        $key = 'billingfox_preview';
        $my_saved_attachment_post_id = get_option( 'billingfox_preview_id', 0 );

        ?><script type='text/javascript'>

        jQuery( document ).ready( function( $ ) {

            if (!wp.media) {
                return;
            }

            // Uploading files
            var file_frame;
            var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
            var set_to_post_id = <?php echo $my_saved_attachment_post_id; ?>; // Set this

            jQuery('#remove_image_button').on('click', function(event) {
                event.preventDefault();

                $( '#<?php echo $key; ?>_attachment' ).attr( 'src', '' ).hide();
                $( '#<?php echo $key; ?>_id' ).val( '');
                $('#remove_image_button').hide();
            });
            jQuery('#upload_image_button').on('click', function( event ){

                event.preventDefault();

                // If the media frame already exists, reopen it.
                if ( file_frame ) {
                    // Set the post ID to what we want
                    file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
                    // Open frame
                    file_frame.open();
                    return;
                } else {
                    // Set the wp.media post id so the uploader grabs the ID we want when initialised
                    wp.media.model.settings.post.id = set_to_post_id;
                }

                // Create the media frame.
                file_frame = wp.media.frames.file_frame = wp.media({
                    title: 'Select a image to upload',
                    button: {
                        text: 'Use this image'
                    },
                    multiple: false	// Set to true to allow multiple files to be selected
                });

                // When an image is selected, run a callback.
                file_frame.on( 'select', function() {
                    // We set multiple to false so only get one image from the uploader
                    attachment = file_frame.state().get('selection').first().toJSON();

                    // Do something with attachment.id and/or attachment.url here
                    $( '#<?php echo $key; ?>_attachment' ).attr( 'src', attachment.url ).css( 'width', 'auto' );
                    $( '#<?php echo $key; ?>_attachment' ).show()
                    $( '#<?php echo $key; ?>_id' ).val( attachment.id );

                    $('#remove_image_button').show();
                    // Restore the main post ID
                    wp.media.model.settings.post.id = wp_media_post_id;
                });

                // Finally, open the modal
                file_frame.open();
            });

            // Restore the main ID when the add media button is pressed
            jQuery( 'a.add_media' ).on( 'click', function() {
                wp.media.model.settings.post.id = wp_media_post_id;
            });
        });

    </script><?php
    }

    private function renderInfo($text)
    {
        echo "<p>$text</p>";
    }

    private function renderImage($post, $label, $key, $help = null)
    {
        $string = <<<HTML
<p class="post-attributes-label-wrapper">
    <label class="post-attributes-label" for="upload_image_button">{{label}}</label>
</p>
<div class='image-preview-wrapper'>
    <img id='{{url_key}}' src='{{url}}' width='100' height='100' style='max-height: 100px; width: 100px; {{url_style}}'>
</div>

<table>
    <tr>
        <td>
            <input id="upload_image_button" type="button" class="button" value="{{change}}" />
        </td>
        <td>
            <input id="remove_image_button" type="button" class="button" value="{{clear}}" style="{{clear_style}}"/>
        </td>
    </tr>
</table>

<input type="hidden" name="{{key}}" id="{{key}}" value="{{value}}">
        
<p class="howto">{{help}}</p>
HTML;
        $value = get_post_meta($post->ID, $key, true);

        echo $this->getTemplating()->render(
            $string,
            [
                'key' => $key,
                'label' => $label,
                'change' =>  __('Change', BILLING_FOX_TRANSLATE),
                'clear' => __('Clear', BILLING_FOX_TRANSLATE),
                'clear_style' => empty($value) ? 'display:none' : '',
                'url_key' => str_replace('_id', '_attachment', $key),
                'url' => wp_get_attachment_url($value),
                'url_style' => empty($value) ? 'display:none' : '',
                'value' => $value,
                'help' => $help
            ]
        );
    }

    private function renderChoice($post, $label, $key, $help = null)
    {

    }

    private function renderNumber($post, $label, $key, $help = null)
    {
        $string = <<<HTML
<p class="post-attributes-label-wrapper">
    <label class="post-attributes-label" for="{{key}}">{{label}}</label>
</p>

<input type="number" step="1" value="{{value}}" name="{{key}}" id="{{key}}">

<p class="howto">{{help}}</p>
HTML;

        echo $this->getTemplating()->render(
            $string,
            [
                'key' => $key,
                'label' => $label,
                'value' => get_post_meta($post->ID, $key, true),
                'help' => $help
            ]
        );
    }

    private function renderText($post, $label, $key, $help = null)
    {
        $string = <<<HTML
<p class="post-attributes-label-wrapper">
    <label class="post-attributes-label" for="{{key}}">{{label}}</label>
</p>

<input type="text" step="1" value="{{value}}" name="{{key}}" id="{{key}}">

<p class="howto">{{help}}</p>
HTML;

        echo $this->getTemplating()->render(
            $string,
            [
                'key' => $key,
                'label' => $label,
                'value' => get_post_meta($post->ID, $key, true),
                'help' => $help
            ]
        );
    }
}