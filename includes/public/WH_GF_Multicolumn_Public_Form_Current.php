<?php

/**
 * The public-specific functionality of the plugin.
 *
 * @link       https://wordpress.org/plugins/gf-form-multicolumn/
 * @since      3.1.0
 *
 * @package    gf-form-multicolumn
 * @subpackage gf-form-multicolumn/includes/public
 */

namespace WH\GF\Multicolumn\Site;

use Exception;

class WH_GF_Multicolumn_Public_Form_Current {
    const LAYOUT_LEGACY_PRE_25 = 'legacy_pre_25';
    const LAYOUT_GF_25_TO_29   = 'gf_25_to_29';
    const LAYOUT_GF_2_10_PLUS    = 'gf_210_plus';
	private $gfLegacyVersion;
    private $formId = 0;
	private $rowColumnArray = [];
	private $columnCounter;
	private $rowCounter = 1;

	private $instanceCounter;

	public function __construct( $version, $form ) {
		try {
			$this->instanceCounter = 0;

            $this->formId = isset( $form['id'] ) ? (int) $form['id'] : 0;

            // Stores form legacy value for ul/li (1), or divs (2) layout
            $this->gfLegacyVersion = isset( $form['markupVersion'] ) ? (int) $form['markupVersion'] : 1;

            $this->calculate_row_and_column_count( $form );

			$this->structure_form_elements();
            //$this->get_layout_mode();
		}
		catch ( Exception $e ) {
			error_log( $e->getMessage() );
		}
	}

    private function is_current_form( $form ): bool {
        $form_id = is_array( $form ) && isset( $form['id'] )
            ? (int) $form['id']
            : 0;

        return $form_id === (int) $this->formId;
    }

    private function get_gravity_forms_version(): string {
        if ( class_exists( '\GFForms' ) && isset( \GFForms::$version ) ) {
            return (string) \GFForms::$version;
        }

        if ( class_exists( '\GFCommon' ) && isset( \GFCommon::$version ) ) {
            return (string) \GFCommon::$version;
        }

        return '0';
    }

    private function calculate_row_and_column_count( $form ): void {
        $row_count    = 1;
        $column_count = 0;
        $in_row       = false;

        if ( empty( $form['fields'] ) || ! is_array( $form['fields'] ) ) {
            return;
        }

        foreach ( $form['fields'] as $form_field ) {
            $field_type = '';

            if ( is_array( $form_field ) && isset( $form_field['type'] ) ) {
                $field_type = $form_field['type'];
            } elseif ( is_object( $form_field ) && isset( $form_field->type ) ) {
                $field_type = $form_field->type;
            }

            if ( 'column_start' === $field_type ) {
                $column_count = 1;
                $in_row       = true;
            } elseif ( 'column_break' === $field_type && $in_row ) {
                $column_count++;
            } elseif ( 'column_end' === $field_type && $in_row ) {
                $this->rowColumnArray[ $row_count ] = $column_count;
                $row_count++;
                $column_count = 0;
                $in_row       = false;
            }
        }
    }

    private function get_layout_mode(): string {
        $gravity_forms_version = $this->get_gravity_forms_version();
        $markup_version        = (int) $this->gfLegacyVersion;

        $is_modern_markup = 2 === $markup_version;

        $is_gf_2_10_plus    = version_compare( $gravity_forms_version, '2.10', '>=' );
        $is_gf_25_to_2_10   = version_compare( $gravity_forms_version, '2.5', '>=' )
            && version_compare( $gravity_forms_version, '2.10', '<' );

        if ( $is_modern_markup && $is_gf_2_10_plus ) {
            return self::LAYOUT_GF_2_10_PLUS;
        }
        if ( $is_modern_markup && $is_gf_25_to_2_10 ) {
            return self::LAYOUT_GF_25_TO_29;
        }
        return self::LAYOUT_LEGACY_PRE_25;
    }

	private function structure_form_elements() {
		$this->instanceCounter ++;

		$this->rowCounter    = 1;
		$this->columnCounter = 0;

        switch ( $this->get_layout_mode() ) {

            case self::LAYOUT_GF_2_10_PLUS:
                add_filter(
                    'gform_field_container',
                    array( $this, 'define_output_elements_gf_2_10_plus' ),
                    10,
                    6
                );
                break;
            case self::LAYOUT_GF_25_TO_29:
                add_filter(
                    'gform_field_container',
                    array( $this, 'define_output_elements_gf_25_to_29' ),
                    10,
                    6
                );
                break;
            case self::LAYOUT_LEGACY_PRE_25:
            default:
                add_filter(
                    'gform_field_container',
                    array( $this, 'define_output_elements_legacy_pre_25' ),
                    10,
                    6
                );
                break;
        }
	}

    public function define_output_elements_gf_2_10_plus( $field_container, $field, $form ) {
        if ( ! $this->is_current_form( $form ) ) {
            return $field_container;
        }

        $field_type = isset( $field->type ) ? $field->type : '';

        if ( ! $this->is_gfmc_field_type( $field_type ) ) {
            return $field_container;
        }

        $css_class = ! empty( $field->cssClass ) ? ' ' . esc_attr( $field->cssClass ) : '';

        $column_count = isset( $this->rowColumnArray[ $this->rowCounter ] )
            ? (int) $this->rowColumnArray[ $this->rowCounter ]
            : 1;

        if ( 'column_start' === $field_type ) {
            return $this->generate_grid_start_of_row( $css_class, $column_count );
        }

        if ( 'column_break' === $field_type ) {
            return $this->generate_grid_break_in_row( $css_class );
        }

        if ( 'column_end' === $field_type ) {
            return $this->generate_grid_end_of_row();
        }

        return $field_container;
    }

    private function generate_grid_start_of_row( $css_class, $column_count ) {
        $this->columnCounter++;

        return sprintf(
            '<div class="gfmc-row gfmc-row-%1$d" style="--gfmc-cols:%2$d;"><div class="gfmc-col gfmc-col-%3$d%4$s">',
            (int) $this->rowCounter,
            (int) $column_count,
            (int) $this->columnCounter,
            $css_class
        );
    }

    private function generate_grid_break_in_row( $css_class ) {
        $this->columnCounter++;

        return sprintf(
            '</div><div class="gfmc-col gfmc-col-%1$d%2$s">',
            (int) $this->columnCounter,
            $css_class
        );
    }

    private function generate_grid_end_of_row() {
        $this->columnCounter = 0;
        $this->rowCounter++;

        return '</div></div>';
    }


	public function define_output_elements_legacy_pre_25( $field_container, $field, $form ) {
        if ( ! $this->is_current_form( $form ) ) {
            return $field_container;
        }

		if ( $this->is_gfmc_field_type( $field->type ) ) {
			$cssClass   = $field->cssClass !== '' ? ' ' . esc_attr
				($field->cssClass) : '';

            $divisor = ! empty( $this->rowColumnArray[ $this->rowCounter ] )
                ? (int) $this->rowColumnArray[ $this->rowCounter ]
                : 1;

			$widthPercentage = floor( 100 / $divisor );

			if ( $field->type === 'column_start' ) {
				$this->columnCounter ++;

				$htmlOutput = '<li class="gfmc-column gfmc-column'
				              . $widthPercentage . ' gfmc-row-' .
				              $this->rowCounter
				              . '-column gfmc-row-' . $this->rowCounter . '-col-' .
				              $this->columnCounter . '-of-' . $divisor . $cssClass .
				              '"';

				$htmlOutput .= '><ul class="flex-wrapper flex-wrapper-' . $divisor
				               . '">';

				return $htmlOutput;
			}
			if ( $field->type === 'column_break' ) {
				$this->columnCounter ++;

				$htmlOutput = '</ul></li><li class="gfmc-column gfmc-column'
				              . $widthPercentage . ' gfmc-row-' .
				              $this->rowCounter . '-column gfmc-row-' .
				              $this->rowCounter . '-col-' .
				              $this->columnCounter . '-of-' . $divisor . $cssClass . '"';

				$htmlOutput .= '><ul class="flex-wrapper flex-wrapper-' . $divisor
				               . '">';

				return $htmlOutput;
			}
			if ( $field->type === 'column_end' ) {
				$this->columnCounter = 0;
				$this->rowCounter ++;

				return ( '</ul></li>' );
			}
			if ( $field->type === 'row_break' || $field->type === 'page' ) {
				$this->columnCounter = 0;

				return '</ul><ul class="gform_fields top_label form_sublabel_below description_below">';
			}
		}

		return ( $field_container );
	}

	public function define_output_elements_gf_25_to_29( $field_container, $field, $form
	) {
        if ( ! $this->is_current_form( $form ) ) {
            return $field_container;
        }

		if ( $this->is_gfmc_field_type( $field->type ) ) {
			$cssClass   = $field->cssClass !== '' ? ' ' . esc_attr
				($field->cssClass) : '';
			if ( isset ($this->rowColumnArray[ $this->rowCounter ])) {
				$columnWidthCount = $this->rowColumnArray[ $this->rowCounter ] * 2;
				$columnWidth      = is_numeric( ( 100 - $columnWidthCount ) /
				                                $this->rowColumnArray[ $this->rowCounter ] )
					? floor( ( 100 - $columnWidthCount ) /
					         $this->rowColumnArray[ $this->rowCounter ] ) : 1;

				if ( $field->type === 'column_start' ) {
					return ( $this->generate_start_of_row( $cssClass,
					                                       $columnWidth ) );

				}
				if ( $field->type === 'column_break' ) {
					return ( $this->generate_break_in_row( $cssClass,
					                                       $columnWidthCount,
					                                       $columnWidth ) );
				}
				if ( $field->type === 'column_end' ) {
					return ( $this->generate_end_of_row() );
				}
			} elseif ( isset ( $this->rowColumnArray[ $this->rowCounter + 1]
			) ) {
				$this->rowCounter++;
			}
		}

		return ( $field_container );
	}

    private function is_gfmc_field_type( $field_type ): bool {
        return in_array(
            $field_type,
            array( 'column_start', 'column_break', 'column_end' ),
            true
        );
    }

	private function generate_start_of_row( $cssClass, $columnWidth,
		$setWidth	= false
	) {
		$cssClass = !(isset($cssClass)) ? '' : $cssClass;
		$this->columnCounter ++;

		return '<div class="gfmc-container"><div class="gfield gfmc-column gfmc-field'.$cssClass.'">';
	}

	private function generate_break_in_row(
		$cssClass, $columnWidthCount, $columnWidth, $setWidth = false
	) {
		$cssClass = !(isset($cssClass)) ? '' : $cssClass;
		$this->columnCounter ++;

		return '</div><div class="gfield gfmc-column gfmc-field'.$cssClass.'">';
	}

    private function generate_end_of_row() {
        $this->columnCounter = 0;
        $this->rowCounter++;

        return '</div></div>';
    }
}
