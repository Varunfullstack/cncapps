<?php
function pageNavigator($row_count, $start_row)
{
    $page_count =
        ceil($row_count / CONFIG_ROWS_PER_PAGE);

    if (CONFIG_ROWS_PER_PAGE > $start_row) {
        $this_page = 1;
    } else {
        $this_page = ($start_row / CONFIG_ROWS_PER_PAGE) + 1;
    }

    $result_page = $row_count . ' Records found. ';
//	$result_page .= 'Result&nbsp;Page:&nbsp;';


    if ($this_page > 1) {

        $link = SC_HTTP::phpSelf() . '?start_row=' . ($start_row - CONFIG_ROWS_PER_PAGE);

        $result_page .=
            '<A	class="pageNavigator" ';

        $result_page .= SC_Ajax::renderLink($link);

        $result_page .= '">' .
            '<SPAN class="previousNextPage">' .
            '&lt;Previous</SPAN>' .
            '</A>&nbsp;';
    }
    /*
        for( $page = 1; $page <= $page_count; $page ++ ){
            if ( $page == $this_page ){
                $result_page .= '<SPAN class="currentPage">' . $page. '</SPAN>&nbsp;';
            }
            else{
                $link = SC_HTTP::phpSelf() . '?start_row=' .
                    ( ( $page * CONFIG_ROWS_PER_PAGE  ) - CONFIG_ROWS_PER_PAGE );

                $result_page .=
                    '<A	class="pageNavigator" ';

                $result_page .= SC_Ajax::renderLink( $link );

                $result_page .=  '">'.$page . '</A>&nbsp;';
            }
        }
    */
    if ($this_page < $page_count) {

        $link = SC_HTTP::phpSelf() . '?start_row=' . ($start_row + CONFIG_ROWS_PER_PAGE);

        $result_page .=
            '<A	class="pageNavigator" ';

        $result_page .= SC_Ajax::renderLink($link);

        $result_page .= '">' .
            '<SPAN class="previousNextPage">' .
            'Next&gt;</SPAN>' .
            '</A>&nbsp;';
    }

    return $result_page;
} // end function page_navigator()

if (!is_object($business)) {
    SC_Object::staticRaiseError('page_list.php: no business object passed');
}

require_once(CONFIG_PATH_SC_CLASSES . 'page_view.php');
$page_view = new SC_PageView;

//require_once(CONFIG_PATH_SC_CLASSES .	'page_cache.php');
//$cache = new SC_PageCache();

require_once(CONFIG_PATH_SC_CLASSES . 'http.php');
require_once(CONFIG_PATH_SC_CLASSES . 'html.php');
require_once(CONFIG_PATH_SC_CLASSES . 'html_form_select.php');

SC_HTTP::convertRequestVars($business);
SC_HTTP::convertRequestVars($page_view);

$class_name = get_class($business);

$edit_url_tag = $class_name . '_edit_url';
$order_by_tag = $class_name . '_order_by';
$display_fields_tag = $class_name . '_display_fields';
$filter_tag = $class_name . '_filter';
$where_statement_tag = $class_name . '_where_statement';
$page_view_id_tag = $class_name . '_page_view_id';
$page_view_name_tag = $class_name . '_page_view_name';
$start_row_tag = $class_name . '_start_row';

$show_filters_tag = $class_name . '_show_filters';
$show_headings_tag = $class_name . '_show_headings';
$show_fields_tag = $class_name . '_show_fields';
$show_field_chooser_tag
    = $class_name . '_show_field_chooser';
$show_page_views_tag = $class_name . '_show_page_views';
$show_edit_tag = $class_name . '_show_edit';
$show_order_by_tag = $class_name . '_show_order_by';
$show_create_tag = $class_name . '_show_create';

$bool_filter_selection_list =
    array(
        array(
            'description' => 'On',
            'value'       => '1'
        ),
        array(
            'description' => 'Off',
            'value'       => '0'
        ),
        array(
            'description' => 'All',
            'value'       => 'all'
        ),
    );
if (isset($_REQUEST['edit_url'])) {
    $_SESSION[$edit_url_tag] = SC_HTTP::requestVar('edit_url');
}
if (isset($_REQUEST['show_filters'])) {
    $_SESSION[$show_filters_tag] = SC_HTTP::requestVar('show_filters');
}
if (isset($_REQUEST['show_edit'])) {
    $_SESSION[$show_edit_tag] = SC_HTTP::requestVar('show_edit');
}
if (isset($_REQUEST['show_create'])) {
    $_SESSION[$show_create_tag] = SC_HTTP::requestVar('show_create');
}
if (isset($_REQUEST['show_headings'])) {
    $_SESSION[$show_headings_tag] = SC_HTTP::requestVar('show_headings');
}
if (isset($_REQUEST['show_fields'])) {
    $_SESSION[$show_fields_tag] = SC_HTTP::requestVar('show_fields');
    if (SC_HTTP::sessionVar($show_fields_tag)) {
        $_SESSION[$show_filters_tag] = true;
    }
}
if (isset($_REQUEST['show_field_chooser'])) {
    $_SESSION[$show_field_chooser_tag] = SC_HTTP::requestVar('show_field_chooser');
}
if (isset($_REQUEST['show_page_views'])) {
    $_SESSION[$show_page_views_tag] = SC_HTTP::requestVar('show_page_views');
}
$_SESSION[$show_page_views_tag] = 0;

if (isset($_REQUEST['show_order_by'])) {
    $_SESSION[$show_order_by_tag] = SC_HTTP::requestVar('show_order_by');
}

if (isset($_REQUEST['start_row'])) {
    $_SESSION[$start_row_tag] = SC_HTTP::requestVar('start_row');
}

$ajax_validation = false;

if (SC_HTTP::requestVar('order_by')) {
    $_SESSION[$order_by_tag] = SC_HTTP::requestVar('order_by');
    unset($_SESSION[$start_row_tag]);
}

if (SC_HTTP::requestVar('where_statement')) {
    if (SC_HTTP::requestVar('where_statement') == 'clear') {
        $_SESSION[$where_statement_tag] = '';
    } else {
        $_SESSION[$where_statement_tag] = SC_HTTP::requestVar('where_statement');
    }
    unset($_SESSION[$start_row_tag]);
}

if (SC_HTTP::requestVar('display_fields')) {
    $_SESSION[$display_fields_tag] = array();
    foreach (SC_HTTP::requestVar('display_fields') as $field => $value) {
        $_SESSION[$display_fields_tag][] = $field;
    }
}
if (SC_HTTP::requestVar('page_view.page_view_id')) {

    $_SESSION[$page_view_id_tag] = SC_HTTP::requestVar('page_view.page_view_id');

    $page_view->getRow($_SESSION[$page_view_id_tag]);

    if ($page_view->getValue('page_view.order_by') != null) {
        $_SESSION[$order_by_tag] = unserialize($page_view->getValue('page_view.order_by'));
    } else {
        unset($_SESSION[$order_by_tag]);
    }

    if ($page_view->getValue('page_view.filters') != null) {
        $_SESSION[$filter_tag] = unserialize($page_view->getValue('page_view.filters'));
    } else {
        unset($_SESSION[$filter_tag]);
    }

    if ($page_view->getValue('page_view.display_fields') != null) {
        $_SESSION[$display_fields_tag] = unserialize($page_view->getValue('page_view.display_fields'));
    } else {
        unset($_SESSION[$display_fields_tag]);
    }
    $_SESSION[$page_view_name_tag] = $page_view->getValue('page_view.name');
}

if (SC_HTTP::requestVar('create_page_view') || SC_HTTP::requestVar('update_page_view')) {

    if (SC_HTTP::requestVar('create_page_view')) {
        $page_view->getRowByName(
            SC_HTTP::requestVar('page_view.name'),
            $class_name
        );
        $page_view->setValue('page_view.script_name', $class_name);
        $page_view->setValue('page_view.name', SC_HTTP::requestVar('page_view.name'));
    } else {
        $page_view->getRow(SC_HTTP::sessionVar($page_view_id_tag));
    }

    if (isset($_SESSION[$filter_tag])) {
        $page_view->setValue('page_view.filters', serialize($_SESSION[$filter_tag]));
        unset($_SESSION[$start_row_tag]);
    }

    if (isset($_SESSION[$order_by_tag])) {
        $page_view->setValue('page_view.order_by', serialize($_SESSION[$order_by_tag]));
    }

    if (isset($_SESSION[$display_fields_tag])) {
        $page_view->setValue('page_view.display_fields', serialize($_SESSION[$display_fields_tag]));
    }

    if (!$page_view->update()) {
        print "Create of new view failed<BR>	";
    } else {
        $_SESSION[$page_view_id_tag] = $page_view->getPKValue();
    }
}
/*
to be implemented!!!
	$field_sequence_tag = $business->getTableName() . '_field_sequence';

if ( SC_HTTP::requestVar('field_sequence') ){
	$_SESSION[$field_sequence_tag] = SC_HTTP::requestVar('field_sequence');
}
*/


// default
if (!isset($_SESSION[$display_fields_tag])) {
    $_SESSION[$display_fields_tag] = array();
    foreach ($business->fields as $field => $attributes) {
        $_SESSION[$display_fields_tag][] = $field;
    }
}

if (isset($_REQUEST['filter'])) {
    if ($_REQUEST['filter'] == 'clear') {
        $_SESSION[$filter_tag] = array();
    } else {
        foreach ($_REQUEST['filter'] as $field => $value) {
            $_SESSION[$filter_tag][$field] = $value;
        }
    }
}
$results = $business->getRows(
    array(
        'order_by'        => SC_HTTP::sessionVar($order_by_tag),
        'filters'         => SC_HTTP::sessionVar($filter_tag),
        'where_statement' => SC_HTTP::sessionVar($where_statement_tag),
        'start_row'       => SC_HTTP::sessionVar($start_row_tag),
        'row_count'       => 10000 // make it big!
    )
);

$row_count = $business->statement->foundRows();

if (isset($_REQUEST['filter_csv_button'])) {

    $fileName = 'EXPORT.CSV';
    Header('Content-type: text/plain');
    Header('Content-Disposition: attachment; filename=' . $fileName);

    // Headings
    $field_count = 1;

    foreach ($_SESSION[$display_fields_tag] as $field) {

        if ($field_count > 1) {
            echo ',';
        }

        $field_count++;

        echo '"' . $business->getLabel($field) . '"';

    }

    echo "\n";

    // rows
    $row_count = 1;

    while ($row = $results->fetchAssoc()) {

        if ($row_count > 1) {
            echo "\n";
        }

        $row_count++;

        $field_count = 1;

        foreach ($row as $field => $value) {


            if (!in_array($field, SC_HTTP::sessionVar($display_fields_tag))) {

                continue;

            }

            if ($field_count > 1) {
                echo ",";
            }

            $field_count++;

            echo '"' . $value . '"';

        }

    }

    exit;
} // end if csv

$additional_width = 30;                    // allow extra column space of non-form elements

//	if ( !SC_HTTP::isAjaxRequest() ){						// show the doc head

$title = $business->getDisplayName();
if (SC_HTTP::sessionVar($page_view_name_tag)) {
    $title .= ': ' . SC_HTTP::sessionVar($page_view_name_tag);
}
?>
<H2><?php print $title ?></H2>
<?php
if (SC_HTTP::sessionVar($show_page_views_tag)) {
    var_dump('test');
    $filters =
        array(
            'page_view.script_name' => $class_name
        );
    $result_set =
        $page_view->getAllRows(
            false,
            'page_view.name',
            $filters
        );
    ?>
    <!-- page_view_section 	-->
    <?php
    $assoc_array = $result_set->fetchAllAssoc();

    $selection_list = array();                                    // initialise
    foreach ($assoc_array as $index => $row) {
        $selection_list[] = array(
            'description' => $row['page_view.name'],
            'value'       => $row['page_view.page_view_id']
        );
    }
    if (count($selection_list) > 0) {
        ?>
        <table cellspacing="1"
               cellpadding="0"
               width="100%"
        >
            <TR>
                <TD align="left">
                    <form
                            name="page_view_select"
                            action="<?php print SC_HTTP::phpSelf() ?>"
                            method="get"
                    >
                        <?php
                        $form_select = new SC_HTMLFormSelect();
                        $field = 'page_view.page_view_id';
                        $parameters =
                            array(
                                'field_name'     => $field,
                                'id'             => $field,
                                'value'          => SC_HTTP::sessionVar($page_view_id_tag),
                                'on_change'      => 'form.submit()',
                                'size'           => $page_view->getWidth($field),
                                'max_length'     => $page_view->getMaxLength($field),
                                'select_options' => $selection_list
                            );
                        $form_select->render($parameters);
                        if (!SC_HTTP::javascriptIsEnabled()){
                        ?>
                        <input
                                type="submit"
                                name="Switch"
                                value="<?php print SC_String::display('Switch To View') ?>"
                        >
                    </form>
                    <?php
                    if (SC_HTTP::sessionVar($show_fields_tag)) {
                        ?>
                        <form
                                name="update_page_view_form"
                                action="<?php print SC_HTTP::phpSelf() ?>"
                                method="get"
                        >
                            <input
                                    type="submit"
                                    name="update_page_view"
                                    value="<?php print SC_String::display('Update View') ?>"
                            >
                        </form>
                        <?php
                    }
                    }
                    else {
                        if (SC_HTTP::sessionVar($show_fields_tag)) {
                            ?>
                            <input
                                    type="button"
                                    name="update_page_view"
                                    value="<?php print SC_String::display('Update View') ?>"
                                    onClick="document.location='<?php print SC_HTTP::phpSelf() ?>?update_page_view=1'"
                            >
                            <?php
                        } // end if (SC_HTTP::sessionVar( $show_edit_tag ))
                        ?>
                        </form>
                        <?php
                    }
                    ?>
                </TD>
            </TR>
        </TABLE>
        <?PHP
    }
} // End show_page_views

/* Page navigator (ala Google) */
?>
<TABLE width="100%">
    <TR>
        <TD>
            <?php
            $field_count = count(SC_HTTP::sessionVar($display_fields_tag));
            print    pageNavigator($row_count, SC_HTTP::sessionVar($start_row_tag));
            ?>
        </TD>
    </TR>
</TABLE>
<table cellspacing="1"
       cellpadding="1"
       width="100%"
>
    <?php
    if (SC_HTTP::sessionVar($show_filters_tag)) {
        ?>
        <!--  do the filters -->
        <form
                name="filters"
                action="<?php print SC_HTTP::phpSelf() ?>"
                method="get"
                AUTOCOMPLETE="off"
        >
            <TR>
                <?php
                $on_change = 'form.submit()';
                //				$on_change = 'form.submit()';
                foreach ($_SESSION[$display_fields_tag] as $field) {
                    ?>
                    <TH class="filter"
                        nowrap
                        align="center"
                    >
                        <?php
                        /*
                                            if ( $section_cache = $cache->sectionStart('label_' . $field , 24) ) {
                                                print SC_String::display( $business->getLabel( $field ), SC_STRING_FMT_UC_WORDS );
                                                $cache->sectionEnd( $section_cache );
                                            }
                                            ?>
                                        <BR/>
                                        <?php
                        */
                        /*
                        for now, we are going to render boolean filters as a drop-down
                        Checked, Unchecked, all
                        */
                        if ($business->getType($field) == SC_DB_BOOL) {
                            $form_select = new SC_HTMLFormSelect();


                            $parameters =
                                array(
                                    'field_name'     => 'filter[' . $field . ']',
                                    'id'             => 'filter[' . $field . ']',
                                    'value'          => SC_HTTP::sessionArrayVar($filter_tag, $field),
                                    'on_change'      => $on_change,
                                    'none_description'
                                                     => false,
                                    'select_options' => $bool_filter_selection_list
                                );
                            $form_select->render($parameters);
                        } // $business->getType($field) == SC_DB_BOOL
                        else {
                            $on_key_down = $on_change;
                            $style = 'width: 100%';                        // to make the input width = TD width
                            $value = SC_HTTP::sessionArrayVar($filter_tag, $field);

                            $business->setDropdown($field, false); // dont want drop-downs here thanks!

                            SC_HTML::formObject(
                                $business,
                                $field,
                                $value,
                                'filter[' . $field . ']',
                                false,
                                $on_key_down,
                                false,
                                $style,
                                100                        // max length
                            );
                        } // else $business->getType($field) == SC_DB_BOOL
                        ?>
                    </TH>
                    <?php
                } // end foreach
                ?>
                <TH align="center"
                    class="filter"
                    colspan="3"
                    width=<?php print $business->getPercentageWidth(
                        $additional_width,
                        $_SESSION[$display_fields_tag],
                        $additional_width
                    ) ?>%
                >
                    <?php
                    if (!SC_HTTP::javascriptIsEnabled()) {
                        ?>
                        <input
                                type="submit"
                                name="filter_button"
                                value="Filter"
                        >
                        <input
                                type="submit"
                                name="filter_csv_button"
                                value="CSV"
                        >
                        <?php
                    } // end !SC_HTTP::javascriptIsEnabled()
                    ?>
        </form>
        </TH>
        </TR>
        <!-- End filters_section -->
        <?php
    } // End SC_HTTP::sessionVar( $show_filters_tag )
    ?>
    <!-- headings -->
    <form
            name="display_fields_form_heading"
            action="<?php print SC_HTTP::phpSelf() ?>"
            method="get"
    >
        <TR>
            <?php
            foreach ($_SESSION[$display_fields_tag] as $field) {

            $order_by = $field;

            // if are we already sorting by this and it isn't descending order use DESC
            if (
                $field == str_replace(' DESC', '', SC_HTTP::sessionVar($order_by_tag)) &&
                !strpos(SC_HTTP::sessionVar($order_by_tag), 'DESC')
            ) {
                $order_by .= ' DESC';
            }

            ?>
            <TD
                    width="<?php print $business->getPercentageWidth(
                        $field,
                        SC_HTTP::sessionVar($display_fields_tag),
                        $additional_width
                    ) ?>%"
                    align="left"
                    valign="top"
                    class="column_heading"
            >
                <table cellspacing="1"
                       cellpadding="1"
                       width="100%"
                >
                    <TR>
                        <TD nowrap>
                            <?php
                            if (SC_HTTP::sessionVar($show_order_by_tag)) {
                                if ($field == str_replace(' DESC', '', SC_HTTP::sessionVar($order_by_tag))) {
                                    $already_ordered_by = true;
                                    if ($ordered_desc = strpos($order_by, 'DESC')) {
                                        $mouse_out_icon = 'images/up.gif';
                                        $mouse_over_icon = 'images/down.gif';
                                    } else {
                                        $mouse_over_icon = 'images/up.gif';
                                        $mouse_out_icon = 'images/down.gif';
                                    }
                                } else {
                                    $already_ordered_by = false;
                                    $ordered_desc = true;
                                    $mouse_over_icon = 'images/up.gif';
                                    $mouse_out_icon = 'images/spacer_icon.gif';
                                }

                                print '<A ' . SC_Ajax::renderLink(SC_HTTP::phpSelf() . '?order_by=' . $order_by) .

                                    'onmouseover="' . CR .
                                    'if(document.getElementById(\'sort_image_' . $field . '\')){' . CR .
                                    TAB . 'document.getElementById(\'sort_image_' . $field . '\').src=\'' . $mouse_over_icon . '\';' . CR .
                                    '}"' . CR .
                                    'onmouseout="' . CR .
                                    'if(document.getElementById(\'sort_image_' . $field . '\')){' . CR .
                                    TAB . 'document.getElementById(\'sort_image_' . $field . '\').src=\'' . $mouse_out_icon . '\';' . CR .
                                    '}"' .

                                    '>';

                            }
                            //							if ( $section_cache = $cache->sectionStart('label_' . $field , 24) ) {
                            print SC_String::display($business->getLabel($field), SC_STRING_FMT_UC_WORDS);
                            //								$cache->sectionEnd( $section_cache );
                            //							}
                            /*
                            if we are ordering by this field then display an icon else display spacer to stop jumping
                            */
                            if ($already_ordered_by) {

                                if ($ordered_desc) {
                                    ?>
                                    <!-- descending order image -->
                                    <img class="icon"
                                         id="sort_image_<?php print $field ?>"
                                         src="images/up.gif"
                                         width="10"
                                         height="10"
                                         border="0"
                                    >
                                    <?php
                                } else {
                                    ?>
                                    <!-- ascending order image -->
                                    <img class="icon"
                                         id="sort_image_<?php print $field ?>"
                                         src="images/down.gif"
                                         width="10"
                                         height="10"
                                         border="0"
                                    >
                                    <?php
                                }
                            } else {
                                ?>
                                <img class="icon"
                                     id="sort_image_<?php print $field ?>"
                                     src="images/spacer_icon.gif"
                                     width="10"
                                     height="10"
                                     border="0"
                                />
                                <?php
                            } // end if ($field == str_replace(' DESC', '' , SC_HTTP::sessionVar( $order_by_tag ) ) )
                            ?>
                            </A>
                        </TD>
                        <TD align="right">
                            <?php
                            if (SC_HTTP::javascriptIsEnabled() && SC_HTTP::sessionVar($show_fields_tag)) {
                                ?>
                                <input
                                        type="checkbox"
                                        class="checkbox"
                                        id="display_fields[<?php print $field ?>]"
                                        onCheck="form.submit()"
                                        onUncheck="form.submit()"
                                        onChange="form.submit()"
                                        onClick="form.submit()"
                                        name="display_fields[<?php print $field ?>]"
                                    <?php print in_array(
                                        $field,
                                        SC_HTTP::sessionVar($display_fields_tag)
                                    ) ? SC_HTML_CHECKED : '' ?>
                                        value="1"
                                >
                                <?php
                            } else {
                                ?>
                                &nbsp;
                                <?php
                            }
                            ?>
                        </TD>
                    </TR>
                </TABLE>
                <?php
                } // end foreach ($_SESSION[$display_fields_tag] as $field)
                ?>
            </TD>
            <TD class="column_heading"
                width="<?php print $business->getPercentageWidth(
                    $additional_width,
                    $_SESSION[$display_fields_tag],
                    $additional_width
                ) ?>%"
            >&nbsp;
            </TD>
        </TR>
        <!-- End headings -->
        <!-- Start rows -->
        <?PHP
        $back = SC_HTTP::phpSelf() . '?' . SC_HTTP::queryString();
        while ($row = $results->fetchAssoc()) {
            ?>
            <tr
                <?php
                $active_field = $business->getTableFieldName('active');
                if (
                    $business->fieldExists($active_field) &&
                    !$row[$active_field]
                ) {
                    print 'class="inactive"';
                }
                ?>
                    onMouseOver="this.bgColor='#FFFFCC';"
                    onMouseOut="this.bgColor='';"
            >
                <?php

                foreach ($row as $field => $value) {
                    if (!in_array($field, SC_HTTP::sessionVar($display_fields_tag))) {
                        continue;
                    }
                    if ($business->isNumericType($field) && $business->getType($field) != SC_DB_ID) {
                        if (!isset($totals[$field])) {
                            $totals[$field] = 0;
                        }
                        $totals[$field] += $value;
                    }
                    if (!$align = $business->getAlign($field)) {
                        $align = $business->isNumericType($field) ? 'right' : 'left';
                    }
                    ?>
                    <td
                            nowrap="nowrap"
                            align="<?php print $align ?>"
                            width="<?php print $business->getPercentageWidth(
                                $field,
                                $_SESSION[$display_fields_tag],
                                $additional_width
                            ) ?>%"
                    >
                        <?php

                        /* Be intuitive about field rendering based upon type */
                        switch ($business->getType($field)) {

                            /* file name so render view link */
                            case SC_DB_FILE_NAME:
                                $name = $row[$business->getPKName()] . '_' . $row[$business->getTableFieldName(
                                        'file_name'
                                    )];
                                $mime_type = $row[$business->getTableFieldName('file_mime_type')];
                                $length = $row[$business->getTableFieldName('file_length')];

                                $url =
                                    'file_view.php?name=' . $name . '&mime_type=' . $mime_type;

                                ?>
                                <A href="<?php print $url ?>"
                                   target="_blank"
                                >
                                    <img
                                            border="0"
                                            src="images/<?php print SC_Mime::getIconFilename($mime_type) ?>"
                                            alt="Display <?php print $value ?>"
                                            title="Display <?php print $value ?>"
                                            class="icon"
                                    >
                                    <?php
                                    print SC_String::display($value)
                                    ?>
                                </A>
                                <?php
                                break;

                            /* show a tick or cross icon for boolean fields */
                            case SC_DB_BOOL:
                                switch ($value) {
                                    case 0:
                                        ?>
                                        <img class="icon"
                                             src="images/ico_cross.gif"
                                             border="0"
                                        >
                                        <?php
                                        break;
                                    case 1:
                                        ?>
                                        <img class="icon"
                                             src="images/ico_tick.gif"
                                             border="0"
                                        >
                                        <?php
                                        break;
                                } // end switch
                                break;

                            /* present an emailto link */
                            case SC_DB_EMAIL:

                                if ($email = $row[$field]) {

                                    $url =
                                        'mailto:' . $email;

                                    ?>
                                    <A href="<?php print $url ?>">
                                        <?php print $value ?>
                                    </A>
                                    <?php
                                }
                                break;

                            default:
                                if (SC_HTTP::sessionVar($edit_url_tag)) {
                                    $edit_url = SC_HTTP::sessionVar($edit_url_tag) .
                                        $row[$business->getPKName()] .
                                        '&cancel_page=' . $back .
                                        '&save_page=' . $back;
                                } else {
                                    $edit_url =
                                        SC_HTTP::phpSelf() .
                                        '?detail_request_script=' .
                                        $business->getTableName() . '.php' .
                                        '&' . $business->getPKName() . '=' . $row[$business->getPKName()] .
                                        '&cancel_page=' . $back .
                                        '&save_page=' . $back;
                                }
                                print
                                    '<A ' .
                                    SC_Ajax::renderLink($edit_url) .
                                    '>' .
                                    SC_String::display($value) .
                                    '</A>';
                                break;
                        } // end switch
                        ?>
                    </td>
                    <?php
                } // end foreach( $row as $field => $value )
                ?>
            </TR>

            <?php
        } // while ( $row = $result_set->fetchAssoc()
        // and now the totals
        ?>
        <tr
                onMouseOver="this.bgColor='#FFFFCC';"
                onMouseOut="this.bgColor='';"
        >
            <?php
            foreach ($_SESSION[$display_fields_tag] as $field) {
                ?>
                <td
                        nowrap="nowrap"
                        align="<?php print $business->isNumericType($field) ? 'right' : 'left' ?>"
                        width="<?php print $business->getPercentageWidth(
                            $field,
                            $_SESSION[$display_fields_tag],
                            $additional_width
                        ) ?>%"
                >
                    <?php
                    if (isset($totals[$field])) {
                        print $totals[$field];
                    } else {
                        ?>
                        &nbsp;
                        <?php
                    }
                    ?>
                </td>
                <?php
            } // end foreach( $row as $field => $value )
            ?>
        </TR>
    </form>
</table>
<BR/>
<TABLE class="fieldChooser">
    <?php
    if (SC_HTTP::sessionVar($show_field_chooser_tag)) {
        ?>
        <form
                name="display_fields_form"
                action="<?php print SC_HTTP::phpSelf() ?>"
                method="get"
        >
            <TR>
                <TH colspan="4"
                    class="column_heading"
                >
                    <a <?php print SC_Ajax::renderLink(SC_HTTP::phpSelf() . '?show_field_chooser=0') ?>>Hide Field
                        Chooser</a>
                </TH>
            </TR>
            <TR>
                <TH colspan="1"
                    class="column_heading"
                >
                    Field
                </TH>
                <TH colspan="1"
                    class="column_heading"
                >
                    Show
                </TH>
                <TH colspan="1"
                    class="column_heading"
                >
                    Order
                </TH>
                <TH colspan="1"
                    class="column_heading"
                >
                    Filter
                </TH>
            </TR>
            <?php
            foreach ($business->fields as $field => $attributes) {
                ?>
                <TR onMouseOver="this.bgColor='#FFFFCC';"
                    onMouseOut="this.bgColor='';"
                >
                    <TD nowrap
                        class="formLabel"
                        width="50%"
                    >
                        <?php
                        //					if ( $section_cache = $cache->sectionStart('label_' . $field , 24) ) {
                        print SC_String::display($business->getLabel($field), SC_STRING_FMT_UC_WORDS);
                        //						$cache->sectionEnd( $section_cache );
                        //					}
                        ?>
                    </TD>

                    <TD width="15%">
                        <input
                                type="checkbox"
                                class="checkbox"
                                id="display_fields[<?php print $field ?>]"
                                onCheck="form.submit()"
                                onUncheck="form.submit()"
                                onChange="form.submit()"
                                onClick="form.submit()"
                                name="display_fields[<?php print $field ?>]"
                            <?php print in_array(
                                $field,
                                SC_HTTP::sessionVar($display_fields_tag)
                            ) ? SC_HTML_CHECKED : '' ?>
                                value="1"
                        >
                    </TD>

                    <TD width="5%">
                        <?php
                        if ($field == str_replace(' DESC', '', SC_HTTP::sessionVar($order_by_tag))) {
                            $order_by = SC_HTTP::sessionVar($order_by_tag);
                            if (strpos($order_by, 'DESC')) {
                                ?>
                                <!-- ascending order image -->
                                <img class="icon"
                                     src="images/down.gif"
                                     width="10"
                                     height="10"
                                     border="0"
                                >
                                <?php
                            } else {
                                ?>
                                <!-- descending order image -->
                                <img class="icon"
                                     src="images/up.gif"
                                     width="10"
                                     height="10"
                                     border="0"
                                >
                                <?php
                            } // end if ( strpos( $order_by , 'DESC' ) )
                        } // end ( $field == str_replace(' DESC', '' , SC_HTTP::sessionVar( $order_by_tag ) ) )
                        ?>
                    </TD>
                    <TD width="30%">
                        <?php
                        $value = SC_HTTP::sessionArrayVar($filter_tag, $field);
                        if ($business->getType($field) == SC_DB_BOOL) {
                            switch ($value) {
                                case 'all':
                                    print
                                        '<img class="icon" src="images/ico_tick.gif" border="0">' .
                                        '<img class="icon" src="images/ico_cross.gif" border="0">';
                                    break;
                                case 0:
                                    print    '<img class="icon" src="images/ico_cross.gif" border="0">';
                                    break;
                                case 1:
                                    print    '<img class="icon" src="images/ico_tick.gif" border="0">';
                                    break;
                            } // end switch ( $value )
                        } else {
                            print $value;
                        } // end ( $business->getType($field) == SC_DB_BOOL )
                        ?>
                    </TD>
                </TR>
                <?php
            } // end foreach ($business->fields as $field => $attributes)
            ?>
            <TR>
                <TD colspan="4">
                    <?php
                    if (!SC_HTTP::javascriptIsEnabled()) {
                        ?>
                        <input
                                type="submit"
                                name="select_button"
                                value="Select"
                        >
                        <?php
                    } // end if ( !SC_HTTP::javascriptIsEnabled() )
                    ?>
                </TD>
            </TR>
        </form>
        <?php
    } //end ( SC_HTTP::sessionVar( $show_fields_tag ) )
    else {
        ?>
        <TR>
            <TH colspan="4"
                class="column_heading"
            >
                <a <?php print SC_Ajax::renderLink(SC_HTTP::phpSelf() . '?show_field_chooser=1') ?>>Show Field
                    Chooser</a>
            </TH>
        </TR>
        <?php
    }
    ?>
</TABLE>
