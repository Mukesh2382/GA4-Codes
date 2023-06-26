<?php
namespace WDMCommonFunctions;

class FccWithIr
{
    private $fccItemId;
    public static $instance;

    // To add expensive codes and to prevent direct object instantiation
    private function __construct()
    {
        $this->fccItemId = 33523;
        add_action('edd_receipt_files', array($this,'getIrUrl'), 10, 5);
        add_action('edd_receipt_bundle_files', array($this,'getIrUrlBundle'), 10, 6);
    }

    // Checks if the current payment being viewed has Fcc Product
    public function hasFcc($payment_id)
    {
        if (class_exists('EDD_Payment')) {
            $payment = new \EDD_Payment($payment_id);
            if (is_array($payment->downloads)) {
                foreach ($payment->downloads as $download) {
                    if ($this->fccItemId == $download['id']) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    // Get url for single product
    public function getIrUrl($filekey, $file, $item_id, $payment_id, $meta)
    {
        if ($this->hasFcc($payment_id)) {
            echo $this->getIrUrlTag();
        }
        unset($filekey);
        unset($file);
        unset($item_id);
        unset($meta);
        return '';
    }

    // Get url for bundle
    public function getIrUrlBundle($filekey, $file, $item_id, $bundle_item, $payment_id, $meta)
    {
        if ($this->hasFcc($payment_id)) {
            return $this->getIrUrlTag();
        }
        unset($filekey);
        unset($file);
        unset($item_id);
        unset($bundle_item);
        unset($meta);
        return '';
    }

    // Get file url html
    public function getIrUrlTag()
    {
        return '<li class="edd_download_file"> <a href="https://wisdmlabs.com/site/index.php?eddfile=316824%3A20277%3A0%3A2&ttl=2442809144&file=0&token=1ada400b1355566bf2f9239534967ec8b19a55522567490dedead0ee771a5c64" class="edd_download_file_link">Instructor Role</a></li>';
    }

    // To get object of the current class
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new FccWithIr();
        }
        return self::$instance;
    }
}
