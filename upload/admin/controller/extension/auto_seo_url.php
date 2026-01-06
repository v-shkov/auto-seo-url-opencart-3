<?php
class ControllerExtensionAutoSeoUrl extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/auto_seo_url');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/auto_seo_url');
        $this->load->model('setting/setting');

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
            $settings = array();
            $settings['auto_seo_url_cron_status'] = !empty($this->request->post['auto_seo_url_cron_status']) ? 1 : 0;
            $this->model_setting_setting->editSetting('auto_seo_url', $settings);

            if (isset($this->request->post['action_generate'])) {
                $count = $this->model_extension_auto_seo_url->generateAll();
                $this->session->data['success'] = sprintf($this->language->get('text_success_generate'), $count);
            } else {
                $this->session->data['success'] = $this->language->get('text_success_save');
            }

            $this->response->redirect($this->url->link('extension/auto_seo_url', 'user_token=' . $this->session->data['user_token'], true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_cron_status'] = $this->language->get('text_cron_status');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['entry_generate'] = $this->language->get('entry_generate');
        $data['entry_cron_path'] = $this->language->get('entry_cron_path');
        $data['button_generate'] = $this->language->get('button_generate');
        $data['button_save'] = $this->language->get('button_save');
        $data['text_cron_title'] = $this->language->get('text_cron_title');
        $data['text_cron_info'] = $this->language->get('text_cron_info');
        $data['text_cron_manual'] = $this->language->get('text_cron_manual');
        $data['text_help']         = $this->language->get('text_help');
        $data['help_auto_seo_url'] = $this->language->get('help_auto_seo_url');
        $data['text_footer_help']  = $this->language->get('text_footer_help');

        if (isset($this->request->post['auto_seo_url_cron_status'])) {
            $data['auto_seo_url_cron_status'] = (int)$this->request->post['auto_seo_url_cron_status'];
        } else {
            $data['auto_seo_url_cron_status'] = (int)$this->config->get('auto_seo_url_cron_status');
        }

        $root_path = rtrim(str_replace('\\', '/', dirname(DIR_APPLICATION)), '/');

        if (defined('HTTPS_CATALOG')) {
            $base = rtrim(HTTPS_CATALOG, '/');
        } else {
            $base = rtrim(HTTP_CATALOG, '/');
        }

        $data['cron_url'] = $base . '/index.php?route=extension/auto_seo_url/cron';
        $data['cron_link'] = $data['cron_url'];
        $data['cron_php'] = '/usr/bin/php ' . $root_path . '/index.php route=extension/auto_seo_url/cron';
        $data['cron_wget'] = '/usr/bin/wget -q -t 1 -O /dev/null "' . $data['cron_url'] . '"';
        $data['cron_curl'] = 'curl -s "' . $data['cron_url'] . '" > /dev/null 2>&1';

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/auto_seo_url', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/auto_seo_url', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/auto_seo_url', $data));
    }

    private function validate() {
        if (!$this->user->hasPermission('modify', 'extension/auto_seo_url')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}
