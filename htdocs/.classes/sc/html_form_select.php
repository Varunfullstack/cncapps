<?php
/**
 * HTML form select objects
 *
 * @package sc
 * @author Karim Ahmed
 * @version 1.0
 * @copyright Sweetcode Ltd 2005
 * @public
 * @access static
 */
require_once(CONFIG_PATH_SC_CLASSES . 'html_form_object.php');

class SC_HTMLFormSelect extends SC_HTMLFormObject
{

    var $selection_list = array();
    var $none_description = SC_HTML_FORM_OBJECT_MSG_NONE_SELECTED;

    /*
    * @abstact
    */
    function render(
        $field_name,
        $value,

        $select_options = false,                            // array ONLY on this class!!
        $none_description = false,                                        // ONLY on this class!!

        $on_change = false,
        $on_key_down = false,

        $business = false,                        // have to make it a copy so it can be false :-(
        $field = false,

        $is_invalid = false,
        $size = false,
        $on_blur = false,
        $maxLength = false,
        $class = false
    )
    {
        parent::render(
            $field_name,
            $value,
            $on_change,
            $on_key_down,

            $business,                        // have to make it a copy so it can be false :-(
            $field,

            $is_invalid,
            $size,
            $on_blur,
            $maxLength,
            $class
        );

        if ($none_description) {
            $this->none_description = $none_description;
        }

        if (is_object($business) && !$select_options) {
            $this->select_options = $business->getSelectOptions($field);
        } else {
            if ($select_options) {
                $this->select_options = &$select_options;
            } else {
                $this->select_options = array();
            }
        }
        ?>
        <select name="<?php print $this->field_name ?>"
                id="<?php print $this->field_name ?>"
                class="<?php print $this->class ?>"
                value="<?php print $this->value ?>"
                onKeyDown="<?php print $this->on_key_down ?>"
                onChange="<?php print $this->on_change ?>"
                onBlur="<?php print $this->on_blur ?>"
        >
            <?php
            if ($none_description) {
                ?>
                <option value=""><?php print $none_description ?></option>
                <?php
            }
            if ($select_options) {
                foreach ($select_options as $index => $row) {
                    ?>
                    <option
                        <?php print SC_HTML::selected($row['value'], $this->value) ?>
                            value="<?php print $row['value'] ?>"><?php print $row['description'] ?>
                    </option>
                    <?php
                }
            }
            ?>
        </select>
        <?php
    }
}

?>