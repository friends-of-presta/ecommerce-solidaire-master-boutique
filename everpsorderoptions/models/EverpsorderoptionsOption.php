<?php
/**
 * Project : everpsorderoptions
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @link https://www.team-ever.com
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class EverpsorderoptionsOption extends ObjectModel
{
    public $id_field;
    public $id_shop;
    public $active;
    public $manage_quantity;
    public $quantity;
    public $option_title;
    public $option_value;

    public static $definition = array(
        'table' => 'everpsorderoptions_option',
        'primary' => 'id_everpsorderoptions_option',
        'multilang' => true,
        'fields' => array(
            'id_shop' => array(
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isUnsignedInt',
                'required' => false
            ),
            'id_field' => array(
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isUnsignedInt',
                'required' => true
            ),
            'manage_quantity' => array(
                'type' => self::TYPE_BOOL,
                'lang' => false,
                'validate' => 'isBool'
            ),
            'quantity' => array(
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isUnsignedInt'
            ),
            'active' => array(
                'type' => self::TYPE_BOOL,
                'lang' => false,
                'validate' => 'isBool'
            ),
            // lang options
            'option_title' => array(
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml'
            ),
            'option_value' => array(
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml'
            ),
        )
    );

    public static function getFullOptions($id_shop, $id_lang, $quantity = true)
    {
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from(
            'everpsorderoptions_option',
            'ef'
        );
        $sql->leftJoin(
            'everpsorderoptions_option_lang',
            'efl',
            'ef.id_everpsorderoptions_option = efl.id_everpsorderoptions_option'
        );
        $sql->where(
            'ef.id_shop = '.(int)$id_shop
        );
        $sql->where(
            'efl.id_lang = '.(int)$id_lang
        );
        $sql->where(
            'ef.active = 1'
        );
        if ($quantity) {
            $sql->where(
                'ef.manage_quantity = 1'
            );
            $sql->where(
                'ef.quantity > 0'
            );
        }
        return Db::getInstance()->executeS($sql);
    }

    public static function fieldHasOptions($id_field, $id_shop, $quantity = true)
    {
        $sql = new DbQuery;
        $sql->select('COUNT(*)');
        $sql->from(
            'everpsorderoptions_option'
        );
        $sql->where(
            'id_field = '.(int)$id_field
        );
        $sql->where(
            'id_shop = '.(int)$id_shop
        );
        if ($quantity) {
            $sql->where(
                'manage_quantity = 1'
            );
            $sql->where(
                'quantity > 0'
            );
        }
        $count = Db::getInstance()->getValue($sql);
        return (int)$count;
    }
}
