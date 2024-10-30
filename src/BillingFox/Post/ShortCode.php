<?php

class BillingFox_Post_ShortCode extends BillingFox_ContainerAware implements BillingFox_RegistrationInterface
{
    const SHORT_CODE = 'micropay';

    public function register()
    {
        add_shortcode('micropay', [$this, 'contentBlock']);
    }

    public function contentBlock($args, $content) {
        /** @var WP_Post $post */
        global $post;

        /** @var BillingFox_Post_Fence $fence */
        $fence = $this->get(BillingFox_Post_Fence::class);

        $args = shortcode_atts([
            'price' => get_post_meta($post->ID, BillingFox_Admin_MeteredBilling::FIELD_PRICE, true),
            'slug' => null,
            'description' => $post->post_title,
        ], $args);

        if (empty($args['slug'])) {
            $args['slug'] = $fence->getPaymentSlug($post);
        }

        if (empty($args['price'])) {
            ob_start();
            $this->getTemplating()->renderTemplate('fence/error.php', [
                'message' => __('invalid price', BILLING_FOX_TRANSLATE),
            ]);
            $output = ob_get_contents();
            ob_end_clean();

            return $output;
        }

        if (!$fence->canReadSlug($args['slug'])) {
            $fence->remember(
                $args['slug'],
                $args['price'],
                $args['description']
            );

            ob_start();
            $this->getTemplating()->renderTemplate('fence/block.php', $args);
            $output = ob_get_contents();
            ob_end_clean();

            return $output;
        }

        return $content;
    }
}