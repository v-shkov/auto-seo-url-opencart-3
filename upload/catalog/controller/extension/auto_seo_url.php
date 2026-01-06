<?php
class ControllerExtensionAutoSeoUrl extends Controller {
    public function cron() {
        if (!$this->config->get('auto_seo_url_cron_status')) {
            return;
        }

        $this->load->model('extension/auto_seo_url');
        $this->model_extension_auto_seo_url->generateAll();
    }
}
