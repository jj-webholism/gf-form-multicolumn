<?php
/**
 * Test Form_Current frontend HTML structural transformation.
 *
 * @package WH\GF\Multicolumn\Tests
 */

namespace WH\GF\Multicolumn\Tests;

use WP_UnitTestCase;
use WH\GF\Multicolumn\Frontend\Form_Current;

/**
 * Class FormCurrentOutputTest
 */
class FormCurrentOutputTest extends WP_UnitTestCase {

    /**
     * Test that column_start, column_break, and column_end fields are converted into
     * structural row/column elements when Gravity Forms outputs div elements in modern themes.
     */
    public function test_column_fields_are_replaced_with_structural_row_markup_in_modern_themes() {
        // Exact JSON export provided from Gravity Forms
        $json_data = '{"0":{"fields":[{"is_payment":false,"duplicatable":true,"repeatable":true,"type":"column_start","id":1,"formId":7,"label":"Row Start","adminLabel":"","isRequired":false,"size":"large","errorMessage":"","visibility":"visible","inputs":null,"description":"","allowsPrepopulate":false,"inputMask":false,"inputMaskValue":"","inputMaskIsCustom":"","maxLength":"","labelPlacement":"","descriptionPlacement":"","subLabelPlacement":"","placeholder":"","cssClass":"","inputName":"","noDuplicates":false,"defaultValue":"","enableAutocomplete":false,"autocompleteAttribute":"","choices":"","conditionalLogic":"","productField":"","layoutGridColumnSpan":"","enableDisplayInColumns":"","enableEnhancedUI":0,"layoutGroupId":"0be0fc55","fields":""},{"is_payment":false,"duplicatable":true,"repeatable":true,"type":"text","id":3,"formId":7,"label":"Untitled","adminLabel":"","isRequired":false,"size":"large","errorMessage":"","visibility":"visible","inputs":null,"description":"","allowsPrepopulate":false,"inputMask":false,"inputMaskValue":"","inputMaskIsCustom":"","maxLength":"","labelPlacement":"","descriptionPlacement":"","subLabelPlacement":"","placeholder":"","cssClass":"","inputName":"","noDuplicates":false,"defaultValue":"","enableAutocomplete":false,"autocompleteAttribute":"","choices":"","conditionalLogic":"","productField":"","layoutGridColumnSpan":"","enableDisplayInColumns":"","enablePasswordInput":"","enableEnhancedUI":0,"layoutGroupId":"c9adf8f3","fields":""},{"is_payment":false,"duplicatable":true,"repeatable":true,"type":"column_break","id":4,"formId":7,"label":"Column Break","adminLabel":"","isRequired":false,"size":"large","errorMessage":"","visibility":"visible","inputs":null,"description":"","allowsPrepopulate":false,"inputMask":false,"inputMaskValue":"","inputMaskIsCustom":"","maxLength":"","labelPlacement":"","descriptionPlacement":"","subLabelPlacement":"","placeholder":"","cssClass":"","inputName":"","noDuplicates":false,"defaultValue":"","enableAutocomplete":false,"autocompleteAttribute":"","choices":"","conditionalLogic":"","productField":"","layoutGridColumnSpan":"","enableDisplayInColumns":"","enableEnhancedUI":0,"layoutGroupId":"62592dcc","fields":""},{"is_payment":false,"duplicatable":true,"repeatable":true,"type":"text","id":5,"formId":7,"label":"Untitled","adminLabel":"","isRequired":false,"size":"large","errorMessage":"","visibility":"visible","inputs":null,"description":"","allowsPrepopulate":false,"inputMask":false,"inputMaskValue":"","inputMaskIsCustom":"","maxLength":"","labelPlacement":"","descriptionPlacement":"","subLabelPlacement":"","placeholder":"","cssClass":"","inputName":"","noDuplicates":false,"defaultValue":"","enableAutocomplete":false,"autocompleteAttribute":"","choices":"","conditionalLogic":"","productField":"","layoutGridColumnSpan":"","enableDisplayInColumns":"","enablePasswordInput":"","enableEnhancedUI":0,"layoutGroupId":"b92029e3","fields":""},{"is_payment":false,"duplicatable":true,"repeatable":true,"type":"column_break","id":6,"formId":7,"label":"Column Break","adminLabel":"","isRequired":false,"size":"large","errorMessage":"","visibility":"visible","inputs":null,"description":"","allowsPrepopulate":false,"inputMask":false,"inputMaskValue":"","inputMaskIsCustom":"","maxLength":"","labelPlacement":"","descriptionPlacement":"","subLabelPlacement":"","placeholder":"","cssClass":"","inputName":"","noDuplicates":false,"defaultValue":"","enableAutocomplete":false,"autocompleteAttribute":"","choices":"","conditionalLogic":"","productField":"","layoutGridColumnSpan":"","enableDisplayInColumns":"","enableEnhancedUI":0,"layoutGroupId":"7cef0055","fields":""},{"is_payment":false,"duplicatable":true,"repeatable":true,"type":"checkbox","id":7,"formId":7,"label":"Untitled","adminLabel":"","isRequired":false,"size":"large","errorMessage":"","visibility":"visible","choices":[{"text":"First Choice","value":"First Choice","isSelected":false,"price":""},{"text":"Second Choice","value":"Second Choice","isSelected":false,"price":""},{"text":"Third Choice","value":"Third Choice","isSelected":false,"price":""}],"validateState":true,"inputs":[{"id":"7.1","label":"First Choice","name":""},{"id":"7.2","label":"Second Choice","name":""},{"id":"7.3","label":"Third Choice","name":""}],"description":"","allowsPrepopulate":false,"inputMask":false,"inputMaskValue":"","inputMaskIsCustom":"","maxLength":"","labelPlacement":"","descriptionPlacement":"","subLabelPlacement":"","placeholder":"","cssClass":"","inputName":"","noDuplicates":false,"defaultValue":"","enableAutocomplete":false,"autocompleteAttribute":"","conditionalLogic":"","productField":"","layoutGridColumnSpan":"","enableDisplayInColumns":"","enableSelectAll":"","choiceLimit":"","enablePrice":"","enableEnhancedUI":0,"layoutGroupId":"fa13422b","fields":"","selectAllText":"Select All"},{"is_payment":false,"duplicatable":true,"repeatable":true,"type":"column_break","id":8,"formId":7,"label":"Column Break","adminLabel":"","isRequired":false,"size":"large","errorMessage":"","visibility":"visible","inputs":null,"description":"","allowsPrepopulate":false,"inputMask":false,"inputMaskValue":"","inputMaskIsCustom":"","maxLength":"","labelPlacement":"","descriptionPlacement":"","subLabelPlacement":"","placeholder":"","cssClass":"","inputName":"","noDuplicates":false,"defaultValue":"","enableAutocomplete":false,"autocompleteAttribute":"","choices":"","conditionalLogic":"","productField":"","layoutGridColumnSpan":"","enableDisplayInColumns":"","enableEnhancedUI":0,"layoutGroupId":"3376a38f","fields":""},{"is_payment":false,"duplicatable":true,"repeatable":true,"type":"number","id":9,"formId":7,"label":"Number","adminLabel":"","isRequired":false,"size":"large","errorMessage":"","visibility":"visible","inputs":null,"numberFormat":"decimal_dot","description":"","allowsPrepopulate":false,"inputMask":false,"inputMaskValue":"","inputMaskIsCustom":"","maxLength":"","labelPlacement":"","descriptionPlacement":"","subLabelPlacement":"","placeholder":"","cssClass":"","inputName":"","noDuplicates":false,"defaultValue":"","enableAutocomplete":false,"autocompleteAttribute":"","choices":"","conditionalLogic":"","enableCalculation":false,"rangeMin":"","rangeMax":"","calculationFormula":"","productField":"","layoutGridColumnSpan":"","enableDisplayInColumns":"","enableEnhancedUI":0,"layoutGroupId":"45f02e39","fields":""},{"is_payment":false,"duplicatable":true,"repeatable":true,"type":"column_end","id":10,"formId":7,"label":"Row End","adminLabel":"","isRequired":false,"size":"large","errorMessage":"","visibility":"visible","inputs":null,"description":"","allowsPrepopulate":false,"inputMask":false,"inputMaskValue":"","inputMaskIsCustom":"","maxLength":"","labelPlacement":"","descriptionPlacement":"","subLabelPlacement":"","placeholder":"","cssClass":"","inputName":"","noDuplicates":false,"defaultValue":"","enableAutocomplete":false,"autocompleteAttribute":"","choices":"","conditionalLogic":"","productField":"","layoutGridColumnSpan":"","enableDisplayInColumns":"","enableEnhancedUI":0,"layoutGroupId":"fa2e6b8b","fields":""}],"button":{"type":"text","text":"","imageUrl":"","width":"auto","location":"bottom","layoutGridColumnSpan":12},"title":"test of admin checkboxes","description":"","version":"2.10.5","id":7,"markupVersion":2,"nextFieldId":11,"useCurrentUserAsAuthor":true,"postContentTemplateEnabled":false,"postTitleTemplateEnabled":false,"postTitleTemplate":"","postContentTemplate":"","lastPageButton":null,"pagination":null,"firstPageCssClass":null,"gfmcEnableCSS":true,"gfmcEnableJS":false,"is_active":true,"date_created":"2026-08-20 16:49:29","is_trash":false,"confirmations":[{"id":"6a872fe579a80","name":"Default Confirmation","isDefault":true,"type":"message","message":"Thanks for contacting us! We will get in touch with you shortly.","url":"","pageId":"","queryString":""}],"notifications":[{"id":"6a872fe579963","isActive":true,"to":"{admin_email}","name":"Admin Notification","event":"form_submission","toType":"email","subject":"New submission from {form_title}","message":"{all_fields}"}]},"version":"3.0.3"}';

        $decoded_data = json_decode( $json_data, true );
        $form         = $decoded_data[0];

        foreach ( $form['fields'] as $key => $field ) {
            $form['fields'][ $key ] = (object) $field;
        }

        $raw_html = '<div id="gform_fields_7" class="gform_fields top_label form_sublabel_below description_below validation_below">' .
            '<div id="field_7_1" class="gfield gfield--type-column_start gfield--input-type-column_start field_sublabel_below gfield--no-description field_description_below field_validation_below gfield_visibility_visible"><label class="gfield_label gform-field-label" for="input_7_1"><span class="gform-field-label__text">Row Start</span></label></div>' .
            '<div id="field_7_3" class="gfield gfield--type-text gfield--input-type-text field_sublabel_below gfield--no-description field_description_below field_validation_below gfield_visibility_visible"><label class="gfield_label gform-field-label" for="input_7_3"><span class="gform-field-label__text">Untitled</span></label><div class="ginput_container ginput_container_text"><input name="input_3" id="input_7_3" type="text" value="" class="large" aria-invalid="false"></div></div>' .
            '<div id="field_7_4" class="gfield gfield--type-column_break gfield--input-type-column_break field_sublabel_below gfield--no-description field_description_below field_validation_below gfield_visibility_visible"><label class="gfield_label gform-field-label" for="input_7_4"><span class="gform-field-label__text">Column Break</span></label></div>' .
            '<div id="field_7_10" class="gfield gfield--type-column_end gfield--input-type-column_end field_sublabel_below gfield--no-description field_description_below field_validation_below gfield_visibility_visible"><label class="gfield_label gform-field-label" for="input_7_10"><span class="gform-field-label__text">Row End</span></label></div>' .
            '</div>';

        $form_current = new Form_Current( $raw_html, $form );
        $output       = $form_current->get_form();

        $this->assertStringContainsString( 'gfmc_row_start', $output );
        $this->assertStringContainsString( 'gfmc_column_break', $output );
        $this->assertStringContainsString( 'gfmc_row_end', $output );

        $this->assertStringNotContainsString( 'Row Start', $output );
        $this->assertStringNotContainsString( 'Column Break', $output );
        $this->assertStringNotContainsString( 'Row End', $output );
    }

    /**
     * Test that structure_form_elements outputs div wrappers when requested.
     */
    public function test_structure_form_elements_outputs_div_wrappers() {
        $form = array(
            'id'     => 1,
            'fields' => array(
                (object) array( 'id' => 10, 'type' => 'column_start', 'cssClass' => '' ),
            ),
        );

        $raw_html     = '<div id="field_1_10" class="gfield">Row Start</div>';
        $form_current = new Form_Current( $raw_html, $form );

        $output = $form_current->get_form( 'div' );

        $this->assertStringContainsString( '<div id="field_1_10" class="gfield gfmc_row_start"></div>', $output );
    }

    /**
     * Test that structure_form_elements outputs li wrappers when requested.
     */
    public function test_structure_form_elements_outputs_li_wrappers() {
        $form = array(
            'id'     => 1,
            'fields' => array(
                (object) array( 'id' => 10, 'type' => 'column_start', 'cssClass' => '' ),
            ),
        );

        $raw_html     = '<li id="field_1_10" class="gfield">Row Start</li>';
        $form_current = new Form_Current( $raw_html, $form );

        $output = $form_current->get_form( 'li' );

        $this->assertStringContainsString( '<li id="field_1_10" class="gfield gfmc_row_start"></li>', $output );
    }
}