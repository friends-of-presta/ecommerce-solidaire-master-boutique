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

class EverpsorderoptionsField extends ObjectModel
{
    public $type;
    public $id_shop;
    public $is_required;
    public $active;
    public $manage_quantity;
    public $quantity;
    public $field_title;
    public $field_description;

    public static $definition = array(
        'table' => 'everpsorderoptions_field',
        'primary' => 'id_everpsorderoptions_field',
        'multilang' => true,
        'fields' => array(
            'id_shop' => array(
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isUnsignedInt',
                'required' => false
            ),
            'type' => array(
                'type' => self::TYPE_HTML,
                'lang' => false,
                'validate' => 'isGenericName',
                'required' => true
            ),
            'is_required' => array(
                'type' => self::TYPE_BOOL,
                'lang' => false,
                'validate' => 'isBool'
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
            // lang fields
            'field_title' => array(
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml'
            ),
            'field_description' => array(
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml'
            ),
        )
    );

    public static function getOptionsFields($id_shop, $id_lang, $where_option = false, $quantity = true)
    {
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from(
            'everpsorderoptions_field',
            'ef'
        );
        $sql->leftJoin(
            'everpsorderoptions_field_lang',
            'efl',
            'ef.id_everpsorderoptions_field = efl.id_everpsorderoptions_field'
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
        if ($where_option) {
            $sql->where(
                'ef.type = "select"
                OR ef.type = "checkbox"
                OR ef.type = "radio"'
            );
        }
        if ($quantity) {
            $sql->where(
                'ef.manage_quantity = 1'
            );
            $sql->where(
                'ef.quantity > 0'
            );
            $sql->where(
                'ef.type != "select"'
            );
            $sql->where(
                'ef.type != "checkbox"'
            );
            $sql->where(
                'ef.type != "radio"'
            );
        }
        return Db::getInstance()->executeS($sql);
    }
}
