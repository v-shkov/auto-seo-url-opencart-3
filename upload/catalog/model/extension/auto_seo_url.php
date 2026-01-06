<?php
class ModelExtensionAutoSeoUrl extends Model {
    public function generateAll() {
        if (!function_exists('string_translit') && file_exists(DIR_SYSTEM . 'helper/string.php')) {
            require_once(DIR_SYSTEM . 'helper/string.php');
        }

        if (!function_exists('string_translit')) {
            return 0;
        }

        $generated = 0;

        $query = $this->db->query("
            SELECT p.product_id, pd.language_id, pd.name
            FROM " . DB_PREFIX . "product p
            LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
            LEFT JOIN " . DB_PREFIX . "seo_url su ON (
                su.query = CONCAT('product_id=', p.product_id)
                AND su.language_id = pd.language_id
            )
            WHERE su.seo_url_id IS NULL
        ");

        foreach ($query->rows as $row) {
            if (!empty($row['name'])) {
                $keyword = $this->buildKeyword(string_translit($row['name']));
                if ($keyword) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = 0, language_id = '" . (int)$row['language_id'] . "', query = 'product_id=" . (int)$row['product_id'] . "', keyword = '" . $this->db->escape($keyword) . "'");
                    $generated++;
                }
            }
        }

        $query = $this->db->query("
            SELECT c.category_id, cd.language_id, cd.name
            FROM " . DB_PREFIX . "category c
            LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id)
            LEFT JOIN " . DB_PREFIX . "seo_url su ON (
                su.query = CONCAT('category_id=', c.category_id)
                AND su.language_id = cd.language_id
            )
            WHERE su.seo_url_id IS NULL
        ");

        foreach ($query->rows as $row) {
            if (!empty($row['name'])) {
                $keyword = $this->buildKeyword(string_translit($row['name']));
                if ($keyword) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = 0, language_id = '" . (int)$row['language_id'] . "', query = 'category_id=" . (int)$row['category_id'] . "', keyword = '" . $this->db->escape($keyword) . "'");
                    $generated++;
                }
            }
        }

        return $generated;
    }

    private function buildKeyword($base) {
        if (!$base) {
            return '';
        }

        $keyword = $base;
        $i = 1;

        while (true) {
            $check = $this->db->query("SELECT seo_url_id FROM " . DB_PREFIX . "seo_url WHERE keyword = '" . $this->db->escape($keyword) . "' LIMIT 1");
            if (!$check->num_rows) {
                break;
            }
            $keyword = $base . '-' . $i;
            $i++;
        }

        return $keyword;
    }
}
