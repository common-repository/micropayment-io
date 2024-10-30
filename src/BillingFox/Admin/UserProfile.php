<?php

class BillingFox_Admin_UserProfile extends BillingFox_ContainerAware implements BillingFox_RegistrationInterface
{
    public function register()
    {
        add_action('show_user_profile', [$this, 'showProfileFields']);
        add_action('edit_user_profile', [$this, 'showProfileFields']);

        // on confirm change the mail in billingfox
        add_filter( 'send_email_change_email',  [$this, 'changeEmail']);
    }

    public function changeEmail($send, $user, $userdata)
    {
        $user = get_userdata($user['id']);

        if (empty($user)) {
            return;
        }

        /** @var BillingFox_Api_Normalizer $normalizer */
        $normalizer = $this->get(BillingFox_Api_Normalizer::class);
        $user_id = $normalizer->normalizeUser($user);

        /** @var BillingFox_Api_Wrapper $api */
        $api = $this->get(BillingFox_Api_Wrapper::class);
        $api->setIdentity($user_id, $userdata['user_email']);
    }

    public function showProfileFields(WP_User $user)
    {
        $html = <<<HTML
<table class="form-table">
    <tr>
        <th>
            <label for="billingfox_id">{{label}}</label>
        </th>
        <td>
            <input type="text" name="billingfox_id" id="billingfox_id" disabled value="{{value}}" class="regular-text" />
            <p class="description">{{help}}</p>
        </td>
    </tr>
</table>
HTML;

        echo $this->getTemplating()->render($html, [
            'label' => __('BillingFox Id'),
            'value' => esc_attr( get_the_author_meta( 'billingfox_id', $user->ID ) ),
            'help' => __('Id to reference user on Micropayment.io.'),
        ]);
    }
}