/**
 * TOCguide settings-page admin JS.
 *
 * Handles:
 *  – Colour text ↔ swatch sync for design settings.
 *  – Show/hide Reading Guide sub-options when guide mode is toggled.
 *  – Show/hide citation format when citations are toggled.
 *  – Live outline preview, including sizes typed without a unit.
 *  – Section tabs that keep unsaved changes on the page.
 *
 * Vanilla JS, no jQuery, no build step required.
 */
( function () {
	'use strict';

	const admin =
		window.tocguideAdmin && typeof window.tocguideAdmin === 'object'
			? window.tocguideAdmin
			: {};
	const LENGTH_HINT = admin.lengthHints || {
		size: 'Use px, rem, or em. A whole number is saved as pixels. A small decimal is saved as rem.',
		box: 'Use one to four lengths, such as 1rem or 12px 16px. A whole number is saved as pixels.',
		signed: 'Use a length such as -0.02em. A number without a unit is saved as em.',
		number: 'Use a unitless number, such as 1.6.',
	};

	/**
	 * Sync a hex text input with a companion colour swatch (input[type=color]).
	 * Adds a clear button to reset to empty (= "not set").
	 */
	function initColorFields() {
		document
			.querySelectorAll( '.tocguide-color-field' )
			.forEach( function ( wrap ) {
				const text = wrap.querySelector( 'input[type="text"]' );
				const swatch = wrap.querySelector( 'input[type="color"]' );
				if ( ! text || ! swatch ) {
					return;
				}
				const clear = wrap.querySelector( '.tocguide-color-clear' );

				// Text → swatch (only when value is a valid 6-digit hex).
				text.addEventListener( 'input', function () {
					const v = text.value.trim();
					if ( /^#[0-9a-fA-F]{6}$/.test( v ) ) {
						swatch.value = v;
					}
					// 3-digit shorthand → expand for swatch.
					if ( /^#[0-9a-fA-F]{3}$/.test( v ) ) {
						swatch.value =
							'#' +
							v[ 1 ].repeat( 2 ) +
							v[ 2 ].repeat( 2 ) +
							v[ 3 ].repeat( 2 );
					}
				} );

				// Swatch → text.
				swatch.addEventListener( 'input', function () {
					text.value = swatch.value;
					text.dispatchEvent(
						new Event( 'input', { bubbles: true } )
					);
				} );

				// Clear → empty (= use built-in styles).
				if ( clear ) {
					clear.addEventListener( 'click', function ( e ) {
						e.preventDefault();
						text.value = '';
						swatch.value = '#ffffff';
						text.dispatchEvent(
							new Event( 'input', { bubbles: true } )
						);
					} );
				}
			} );
	}

	/**
	 * Show/hide a dependent container based on a checkbox state.
	 *
	 * @param {string} triggerId ID of the controlling checkbox.
	 * @param {string} targetId  ID of the container to show/hide.
	 */
	function syncConditional( triggerId, targetId ) {
		const trigger = document.getElementById( triggerId );
		const target = document.getElementById( targetId );
		if ( ! trigger || ! target ) {
			return;
		}
		function update() {
			target.hidden = ! trigger.checked;
		}
		trigger.addEventListener( 'change', update );
		update();
	}

	/**
	 * Turn a typed size into the CSS length that will be saved.
	 *
	 * @param {string} raw  Field value.
	 * @param {string} kind size, box, signed, or number.
	 * @return {string|null} Normalized value, or null when it cannot be saved.
	 */
	function normalizeLength( raw, kind ) {
		const v = raw.trim();
		if ( '' === v ) {
			return '';
		}
		if ( 'number' === kind ) {
			return /^\d+(\.\d+)?$/.test( v ) ? v : null;
		}
		if ( 'signed' === kind ) {
			if ( /^-?[\d.]+(px|rem|em)$/.test( v ) ) {
				return v;
			}
			if ( /^-?\d+(\.\d+)?$/.test( v ) ) {
				return v + 'em';
			}
			return null;
		}
		const one = function ( part ) {
			if ( '0' === part || /^[\d.]+(%|px|rem|em)$/.test( part ) ) {
				return part;
			}
			if ( /^\d+$/.test( part ) ) {
				return part + 'px';
			}
			if ( /^\d+\.\d+$/.test( part ) ) {
				return parseFloat( part ) >= 8 ? part + 'px' : part + 'rem';
			}
			return null;
		};
		if ( 'box' === kind ) {
			const parts = v.split( /\s+/ );
			if ( parts.length > 4 ) {
				return null;
			}
			const out = [];
			for ( let i = 0; i < parts.length; i++ ) {
				const next = one( parts[ i ] );
				if ( null === next || '' === next ) {
					return null;
				}
				out.push( next );
			}
			return out.join( ' ' );
		}
		return one( v );
	}

	/**
	 * Mark a length field when the typed value would be discarded on save.
	 *
	 * @param {HTMLInputElement} input Length field.
	 */
	function markLengthField( input ) {
		const kind = input.getAttribute( 'data-tocguide-length' ) || 'size';
		const next = normalizeLength( input.value, kind );
		const invalid = null === next;
		input.setAttribute( 'aria-invalid', invalid ? 'true' : 'false' );
		let hint = input.parentNode.querySelector(
			'.tocguide-field-hint[data-for="' + input.id + '"]'
		);
		if ( invalid ) {
			if ( ! hint ) {
				hint = document.createElement( 'p' );
				hint.className = 'tocguide-field-hint';
				hint.setAttribute( 'data-for', input.id );
				if ( ! input.id ) {
					input.id =
						'tocguide-length-' +
						Math.random().toString( 36 ).slice( 2, 8 );
					hint.setAttribute( 'data-for', input.id );
				}
				hint.id = input.id + '-hint';
				input.insertAdjacentElement( 'afterend', hint );
				input.setAttribute( 'aria-describedby', hint.id );
			}
			hint.textContent = LENGTH_HINT[ kind ] || LENGTH_HINT.size;
		} else if ( hint ) {
			hint.remove();
			input.removeAttribute( 'aria-describedby' );
		}
		return next;
	}

	/**
	 * Paint the settings preview from the current form values.
	 * Hidden sections stay in the form, so every tab can update it.
	 */
	function refreshPreview() {
		const nav = document.getElementById( 'tocguide-preview-nav' );
		const form = document.querySelector( '.tocguide-admin__form' );
		if ( ! nav || ! form ) {
			return;
		}

		const nodes = function ( key ) {
			return form.querySelectorAll(
				'[name="tocguide_settings[' + key + ']"]'
			);
		};
		const value = function ( key ) {
			const list = nodes( key );
			if ( ! list.length ) {
				return '';
			}
			if ( 'radio' === list[ 0 ].type ) {
				for ( let i = 0; i < list.length; i++ ) {
					if ( list[ i ].checked ) {
						return list[ i ].value.trim();
					}
				}
				return '';
			}
			return list[ 0 ].value.trim();
		};
		const checked = function ( key ) {
			const list = nodes( key );
			return !! ( list.length && list[ 0 ].checked );
		};
		const cssValue = function ( key ) {
			const el = form.querySelector(
				'[name="tocguide_settings[' + key + ']"]'
			);
			if ( ! el ) {
				return '';
			}
			const kind = el.getAttribute( 'data-tocguide-length' );
			if ( ! kind ) {
				return value( key );
			}
			const next = markLengthField( el );
			return null === next ? '' : next;
		};

		const stacks =
			window.tocguideAdmin && window.tocguideAdmin.fontStacks
				? window.tocguideAdmin.fontStacks
				: {
						system: 'system-ui, "Segoe UI", sans-serif',
						geometric:
							'"Avenir Next", "Segoe UI", system-ui, sans-serif',
						neutral: 'Arial, Helvetica, sans-serif',
						mono: 'ui-monospace, Menlo, Consolas, monospace',
				  };

		const vars = {
			design_bg_color: '--tocguide-bg',
			design_text_color: '--tocguide-color',
			design_link_color: '--tocguide-link-color',
			design_link_hover: '--tocguide-link-hover',
			design_accent_color: '--tocguide-accent',
			design_marker_color: '--tocguide-marker-bg',
			design_marker_text: '--tocguide-marker-color',
			design_font_size: '--tocguide-font-size',
			design_font_weight: '--tocguide-font-weight',
			design_title_size: '--tocguide-title-size',
			design_title_weight: '--tocguide-title-weight',
			design_line_height: '--tocguide-line-height',
			design_letter_spacing: '--tocguide-letter-spacing',
			design_item_gap: '--tocguide-item-gap',
			design_border_width: '--tocguide-border-width',
			design_border_color: '--tocguide-border-color',
			design_border_style: '--tocguide-border-style',
			design_border_radius: '--tocguide-radius',
			design_padding: '--tocguide-padding',
			design_title_color: '--tocguide-title-color',
			design_icon_color: '--tocguide-icon-color',
			design_text_transform: '--tocguide-text-transform',
		};

		Object.keys( vars ).forEach( function ( key ) {
			const raw = cssValue( key );
			if ( '' === raw ) {
				nav.style.removeProperty( vars[ key ] );
			} else {
				nav.style.setProperty( vars[ key ], raw );
			}
		} );

		const family = value( 'design_font_family' );
		let stack = stacks[ family ] || '';
		if ( ! stack && checked( 'exclude_theme_styles' ) && stacks.system ) {
			stack = stacks.system;
		}
		if ( stack ) {
			nav.style.setProperty( '--tocguide-font-family', stack );
		} else {
			nav.style.removeProperty( '--tocguide-font-family' );
		}

		const styles = [ 'default', 'minimal', 'boxed', 'underline', 'card' ];
		const style =
			styles.indexOf( value( 'auto_style' ) ) >= 0
				? value( 'auto_style' )
				: 'default';
		styles.forEach( function ( name ) {
			nav.classList.remove( 'is-style-' + name, 'tocguide--' + name );
		} );
		nav.classList.add( 'is-style-' + style, 'tocguide--' + style );

		nav.classList.toggle(
			'is-theme-isolated',
			checked( 'exclude_theme_styles' )
		);
		nav.classList.toggle( 'is-compact', checked( 'auto_compact' ) );
		nav.classList.toggle( 'is-no-markers', checked( 'auto_hide_markers' ) );

		const colorKeys = [
			'design_bg_color',
			'design_text_color',
			'design_link_color',
			'design_accent_color',
			'design_title_color',
			'design_marker_color',
		];
		const hasColors = colorKeys.some( function ( key ) {
			return /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test( value( key ) );
		} );
		nav.classList.toggle( 'has-design-colors', hasColors );

		const marker = value( 'design_marker_style' );
		nav.classList.toggle( 'is-marker-square', 'square' === marker );
		nav.classList.toggle( 'is-marker-plain', 'plain' === marker );

		const shadow = value( 'design_shadow' );
		nav.classList.toggle( 'has-shadow-soft', 'soft' === shadow );
		nav.classList.toggle( 'has-shadow-medium', 'medium' === shadow );

		const focus = value( 'focus_style' );
		if ( 'bold' === focus || 'high-contrast' === focus ) {
			nav.setAttribute( 'data-tocguide-focus', focus );
		} else {
			nav.removeAttribute( 'data-tocguide-focus' );
		}
	}

	/**
	 * Rewrite a length field to the value that will be saved.
	 *
	 * @param {HTMLInputElement} input Length field.
	 */
	function commitLengthField( input ) {
		const kind = input.getAttribute( 'data-tocguide-length' );
		if ( ! kind ) {
			return;
		}
		const next = normalizeLength( input.value, kind );
		if ( null !== next && next !== input.value.trim() ) {
			input.value = next;
			input.dispatchEvent( new Event( 'input', { bubbles: true } ) );
		}
	}

	/**
	 * Keep section switches on this page so unsaved design changes stay put.
	 *
	 * @param {HTMLFormElement} form Settings form.
	 */
	function initSections( form ) {
		const tabs = document.querySelectorAll( '.tocguide-admin__section' );
		if ( ! tabs.length ) {
			return;
		}

		const show = function ( tab ) {
			const panelId = tab.getAttribute( 'aria-controls' );
			tabs.forEach( function ( other ) {
				const on = other === tab;
				other.classList.toggle( 'is-active', on );
				other.setAttribute( 'aria-selected', on ? 'true' : 'false' );
			} );
			document
				.querySelectorAll( '[id^="tocguide-section-"]' )
				.forEach( function ( panel ) {
					panel.hidden = panel.id !== panelId;
				} );

			const section = ( panelId || '' ).replace(
				'tocguide-section-',
				''
			);
			if ( section && window.history && window.history.replaceState ) {
				const url = new URL( window.location.href );
				url.searchParams.set( 'section', section );
				window.history.replaceState( null, '', url );
			}
			const referer = form.querySelector( '[name="_wp_http_referer"]' );
			if ( referer && section ) {
				try {
					const ref = new URL(
						referer.value,
						window.location.origin
					);
					ref.searchParams.set( 'section', section );
					referer.value = ref.pathname + ref.search;
				} catch {
					// Leave the referer WordPress printed.
				}
			}
		};

		tabs.forEach( function ( tab, index ) {
			tab.addEventListener( 'click', function ( event ) {
				event.preventDefault();
				show( tab );
			} );
			tab.addEventListener( 'keydown', function ( event ) {
				if (
					'ArrowRight' !== event.key &&
					'ArrowLeft' !== event.key &&
					'Home' !== event.key &&
					'End' !== event.key
				) {
					return;
				}
				event.preventDefault();
				let next = index;
				if ( 'ArrowRight' === event.key ) {
					next = ( index + 1 ) % tabs.length;
				} else if ( 'ArrowLeft' === event.key ) {
					next = ( index - 1 + tabs.length ) % tabs.length;
				} else if ( 'Home' === event.key ) {
					next = 0;
				} else {
					next = tabs.length - 1;
				}
				tabs[ next ].focus();
				show( tabs[ next ] );
			} );
		} );
	}

	let statusTimer = 0;

	/**
	 * Tell assistive tech the sample outline changed, after typing pauses.
	 */
	function announcePreview() {
		const status = document.getElementById( 'tocguide-preview-status' );
		if ( ! status ) {
			return;
		}
		window.clearTimeout( statusTimer );
		statusTimer = window.setTimeout( function () {
			status.textContent = '';
			window.setTimeout( function () {
				status.textContent =
					admin.previewUpdated || 'Outline preview updated.';
			}, 30 );
		}, 400 );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		initColorFields();
		refreshPreview();

		const form = document.querySelector( '.tocguide-admin__form' );
		if ( form ) {
			initSections( form );
			form.addEventListener( 'input', function () {
				refreshPreview();
				announcePreview();
			} );
			form.addEventListener( 'change', function () {
				refreshPreview();
				announcePreview();
			} );
			form.querySelectorAll( '[data-tocguide-length]' ).forEach(
				function ( input ) {
					input.addEventListener( 'blur', function () {
						commitLengthField( input );
					} );
				}
			);
		}

		const preview = document.getElementById( 'tocguide-preview-nav' );
		if ( preview ) {
			preview.addEventListener( 'click', function ( event ) {
				const link = event.target.closest( 'a' );
				if ( link && preview.contains( link ) ) {
					event.preventDefault();
				}
			} );
		}

		// Reading Guide sub-options depend on guide mode being on.
		syncConditional( 'tocguide-auto-guide-mode', 'tocguide-guide-subopts' );

		// Citation format selector depends on citations being on.
		syncConditional(
			'tocguide-auto-show-citations',
			'tocguide-citation-style-row'
		);
	} );
} )();
