<?php
class Peecho_ImportExport
{
    const FILE_CFG = 'peecho-export.cfg';
    const FILE_ZIP = 'peecho-export.zip';
    private $downloadUrl;
    public function exportSnippets()
    {
        if (isset($_POST['Peecho_export'])) {
            $url = $this->createExportFile();
            if ($url) {
                $this->downloadUrl = $url;
                add_action(
                    'admin_footer',
                    array(&$this, 'psnippetsFooter'),
                    10000
                );
            } else {
                echo __('Error: ', Peecho::TEXT_DOMAIN).$url;
            }
        } else {
            // Check if there is any old export files to delete
            $dir = wp_upload_dir();
            $upload_dir = $dir['basedir'] . '/';
            chdir($upload_dir);
            if (file_exists('./'.self::FILE_ZIP)) {
                unlink('./'.self::FILE_ZIP);
            }
        }
    }
    public function importSnippets()
    {
        $import =
        '<br/><br/><strong>'.
        __('Import', Peecho::TEXT_DOMAIN).
        '</strong><br/>';
        if (!isset($_FILES['Peecho_import_file'])
            || empty($_FILES['Peecho_import_file'])
        ) {
            $import .=
            '<p>'.__('Import snippets from a peecho-export.zip file. Importing overwrites any existing snippets.', Peecho::TEXT_DOMAIN).
            '</p>';
            $import .= '<form method="post" enctype="multipart/form-data">';
            $import .= '<input type="file" name="Peecho_import_file"/>';
            $import .= '<input type="hidden" name="action" value="wp_handle_upload"/>';
            $import .=
            '<input type="submit" class="button" value="'.
            __('Import Snippets', Peecho::TEXT_DOMAIN).'"/>';
            $import .= '</form>';
        } else {
            $file = wp_handle_upload($_FILES['Peecho_import_file']);
            
            if (isset($file['file']) && !is_wp_error($file)) {
                require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
                $zip = new PclZip($file['file']);
                $dir = wp_upload_dir();
                $upload_dir = $dir['basedir'] . '/';
                chdir($upload_dir);
                $unzipped = $zip->extract();

                if ($unzipped[0]['stored_filename'] == self::FILE_CFG
                    && $unzipped[0]['status'] == 'ok'
                ) {
                    // Delete the uploaded archive
                    unlink($file['file']);

                    $snippets = file_get_contents(
                        $upload_dir.self::FILE_CFG
                    );

                    if ($snippets) {
                        $snippets = apply_filters(
                            'peecho_import',
                            $snippets
                        );
                        update_option(
                            Peecho::OPTION_KEY,
                            unserialize($snippets)
                        );
                    }

                    // Delete the snippet file
                    unlink('./'.self::FILE_CFG);

                    $import .=
                    '<p><strong>'.
                    __('Snippets successfully imported.', Peecho::TEXT_DOMAIN).
                    '</strong></p>';
                } else {
                    $import .=
                    '<p><strong>'.
                    __('Snippets could not be imported:', Peecho::TEXT_DOMAIN).
                    ' '.
                    __('Unzipping failed.', Peecho::TEXT_DOMAIN).
                    '</strong></p>';
                }
            } else {
                if ($file['error'] || is_wp_error($file)) {
                    $import .=
                    '<p><strong>'.
                    __('Snippets could not be imported:', Peecho::TEXT_DOMAIN).
                    ' '.
                    $file['error'].'</strong></p>';
                } else {
                    $import .=
                    '<p><strong>'.
                    __('Snippets could not be imported:', Peecho::TEXT_DOMAIN).
                    ' '.
                    __('Upload failed.', Peecho::TEXT_DOMAIN).
                    '</strong></p>';
                }
            }
        }
        return $import;
    }
    private function createExportFile()
    {
        $snippets = serialize(get_option(Peecho::OPTION_KEY));
        $snippets = apply_filters('peecho_export', $snippets);
        $dir = wp_upload_dir();
        $upload_dir = $dir['basedir'] . '/';
        $upload_url = $dir['baseurl'] . '/';
        if (!$handle = fopen($upload_dir.'./'.self::FILE_CFG, 'w')) {
            die();
        }
        if (!fwrite($handle, $snippets)) {
            die();
        }
        fclose($handle);
        require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
        chdir($upload_dir);
        $zip = new PclZip('./'.self::FILE_ZIP);
        $zipped = $zip->create('./'.self::FILE_CFG);
        unlink('./'.self::FILE_CFG);

        if (!$zipped) {
            return false;
        }
        
        return $upload_url.'./'.self::FILE_ZIP;
    }

    public function psnippetsFooter()
    {
        $export = '<script type="text/javascript">
                        document.location = \''.$this->downloadUrl.'\';
                   </script>';
        echo $export;
    }
}
