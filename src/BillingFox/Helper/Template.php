<?php

class BillingFox_Helper_Template
{
    /**
     * @param string $template
     * @param string[] $params
     *
     * @return string
     */
    public function render($template, $params)
    {
        foreach ($params as $key => $value) {
            $template = str_replace('{{'.$key.'}}', "".$value, $template);
        }

        return $template;
    }

    /**
     * render template
     *
     * @param $template_name
     * @param array $args
     * @param string $tempate_path
     * @param string $default_path
     */
    public function renderTemplate($template_name, $args = [], $tempate_path = '', $default_path = '')
    {
        if (!empty($args) && is_array($args)) {
            extract($args);
        }

        $template_file = $this->loadTemplate( $template_name, $tempate_path, $default_path );

        if (!file_exists($template_file)) {
            _doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $template_file ), '1.0.0' );

            return;
        }

        // keep this loader -> used in template file
        $loader = $this;

        include $template_file;
    }

    /**
     * render and return template
     *
     * @param $template_name
     * @param array $args
     * @param string $tempate_path
     * @param string $default_path
     *
     * @return string
     */
    public function getTemplate($template_name, $args = [], $tempate_path = '', $default_path = '')
    {
        ob_start();

        $this->renderTemplate($template_name, $args, $tempate_path, $default_path);

        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }

    /**
     * Locate template.
     *
     * Locate the called template.
     * Search Order:
     * 1. /themes/theme/micropayment-io/$template_name
     * 2. /themes/theme/$template_name
     * 3. /plugins/micropayment-io/templates/$template_name.
     *
     * @since 1.0.0
     *
     * @param 	string 	$template_name			Template to load.
     * @param 	string 	$template_path	        Path to templates.
     * @param 	string	$default_path			Default path to template files.
     * @return 	string 							Path to the template file.
     */
    public function loadTemplate($template_name, $template_path = '', $default_path = '')
    {
        if (empty($template_path)) {
            $template_path = BILLING_FOX_PLUGIN_NAME.'/';
        }

        if (empty($default_path)) {
            $default_path = BillingFox_Plugin::getInstance()->root . 'templates/';
        }

        $template = locate_template([
            $template_path . $template_name,
            $template_name
        ]);

        if (empty($template)) {
            $template = $default_path . $template_name;
        }

        return apply_filters('billingfox_locate_template', $template, $template_name, $template_path, $default_path);
    }
}