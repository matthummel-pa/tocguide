/**
 * Editor UI for the Table of Contents block.
 *
 * Canvas uses useBlockProps + RichText. Settings live in InspectorControls.
 * List type is a BlockControls toolbar control. Visual presets are Gutenberg
 * Block Styles from block.json (Styles panel → is-style-* classes).
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 */
import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	InspectorControls,
	BlockControls,
	RichText,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import {
	Disabled,
	PanelBody,
	ToggleControl,
	RangeControl,
	SelectControl,
	TextareaControl,
	ToolbarGroup,
	ToolbarButton,
} from '@wordpress/components';
import { formatListBullets, formatListNumbered } from '@wordpress/icons';
import { collectHeadings, filterAndNormalize, itemMarker } from './headings';

/**
 * Document icon shown to the right of a heading when reader notes are on.
 *
 * @return {Object} SVG element.
 */
function NoteIcon() {
	return (
		<svg
			className="tocguide__svg"
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 24 24"
			width="16"
			height="16"
			fill="none"
			stroke="currentColor"
			strokeWidth="1.75"
			strokeLinecap="round"
			strokeLinejoin="round"
			aria-hidden="true"
			focusable="false"
		>
			<path d="M7.5 4h6l4 4v11.1a1.4 1.4 0 0 1-1.4 1.4H7.5a1.4 1.4 0 0 1-1.4-1.4V5.4A1.4 1.4 0 0 1 7.5 4z" />
			<path d="M13.4 4.2V8.2h4" />
			<path d="M8.6 12.2h3.6" />
			<path d="m11.7 16.7 3.2-3.2.9.9-3.2 3.2h-.9z" />
		</svg>
	);
}

/** @type {Object} Status emoji map */
const STATUS_ICON = { draft: '✏️', progress: '🔄', done: '✅' };

/**
 * Nested outline preview (read-only; front end is server-rendered).
 *
 * @param {Object}  props
 * @param {Array}   props.items
 * @param {boolean} props.ordered
 * @param {string}  props.numbering
 * @param {boolean} props.hideMarkers
 * @param {boolean} props.showReaderNotes
 * @param {boolean} props.isolate         Drop theme list markup.
 * @param {Object}  props.sectionStatus   Writing status map slug→status.
 */
function PreviewList( {
	items,
	ordered,
	numbering,
	hideMarkers,
	showReaderNotes,
	isolate,
	sectionStatus = {},
} ) {
	let Tag = 'ul';
	if ( isolate ) {
		Tag = 'div';
	} else if ( ordered ) {
		Tag = 'ol';
	}
	const Item = isolate ? 'div' : 'li';
	const tree = [];
	const stack = [ tree ];

	items.forEach( ( item ) => {
		while ( stack.length > item.level ) {
			stack.pop();
		}
		while ( stack.length < item.level ) {
			const nested = [];
			const parent = stack[ stack.length - 1 ];
			const last = parent[ parent.length - 1 ];
			if ( last ) {
				last.children = nested;
			} else {
				parent.push( { text: '', slug: '', children: nested } );
			}
			stack.push( nested );
		}
		stack[ stack.length - 1 ].push( { ...item, children: [] } );
	} );

	const renderItems = ( nodes, depth, parentMarker ) => (
		<Tag
			className={ depth === 0 ? 'tocguide__list' : 'tocguide__sub' }
			role={ isolate ? 'list' : undefined }
		>
			{ nodes.map( ( node, index ) => {
				let label = node.text || '';
				if ( isolate && label ) {
					const stripped = String( label ).replace( /^0\.\s+/, '' );
					label = stripped || label;
				}
				const marker = label
					? itemMarker( index, parentMarker, {
							ordered,
							numbering,
							hideMarkers,
					  } )
					: '';
				const childMarker =
					ordered && numbering === 'nested' && marker !== 'bullet'
						? marker
						: '';
				return (
					<Item
						key={ `${ node.slug }-${ index }` }
						className="tocguide__item"
						role={ isolate ? 'listitem' : undefined }
					>
						{ label ? (
							<div className="tocguide__item-row">
								{ marker === 'bullet' ? (
									<span
										className="tocguide__marker tocguide__marker--bullet"
										aria-hidden="true"
									/>
								) : null }
								{ marker && marker !== 'bullet' ? (
									<span
										className="tocguide__marker"
										aria-hidden="true"
									>
										{ marker }
									</span>
								) : null }
								<a
									className="tocguide__link"
									href={ `#${ node.slug }` }
								>
									{ label }
									{ sectionStatus[ node.slug ] && (
										<span
											className="tocguide__status-dot"
											title={ sectionStatus[ node.slug ] }
											aria-hidden="true"
										>
											{ STATUS_ICON[
												sectionStatus[ node.slug ]
											] || '' }
										</span>
									) }
								</a>
								{ showReaderNotes ? (
									<span className="tocguide__item-tools">
										<span
											className="tocguide__icon-btn"
											aria-hidden="true"
										>
											<NoteIcon />
										</span>
									</span>
								) : null }
							</div>
						) : null }
						{ node.children?.length
							? renderItems(
									node.children,
									depth + 1,
									childMarker
							  )
							: null }
					</Item>
				);
			} ) }
		</Tag>
	);

	return renderItems( tree, 0, '' );
}

const Glyph = ( { children } ) => (
	<svg
		className="tocguide__svg"
		xmlns="http://www.w3.org/2000/svg"
		viewBox="0 0 24 24"
		width="16"
		height="16"
		fill="none"
		stroke="currentColor"
		strokeWidth="1.75"
		strokeLinecap="round"
		aria-hidden="true"
		focusable="false"
	>
		{ children }
	</svg>
);

const CloseGlyph = () => (
	<Glyph>
		<path d="M6 6 18 18" />
		<path d="M18 6 6 18" />
	</Glyph>
);

const FocusGlyph = () => (
	<Glyph>
		<circle cx="12" cy="12" r="3" />
		<path d="M12 3v2.5" />
		<path d="M12 18.5V21" />
		<path d="M3 12h2.5" />
		<path d="M18.5 12H21" />
	</Glyph>
);

export default function Edit( { attributes, setAttributes } ) {
	const {
		title,
		showTitle,
		titleTag,
		showH1,
		showH2,
		showH3,
		showH4,
		showH5,
		showH6,
		ordered,
		numbering,
		hideMarkers,
		excludeThemeStyles,
		collapsible,
		collapsedDefault,
		showClose,
		focusMode,
		sticky,
		fixedLeft,
		compact,
		twoColumns,
		underlineLinks,
		highlightActive,
		maxHeight,
		minHeadings,
		smoothScroll,
		scrollOffset,
		previewOnHover,
		guideMode,
		showPreviews,
		showDensity,
		showReadTime,
		trackProgress,
		showReactions,
		showCitations,
		citationStyle,
		sectionNotes,
		sectionStatus,
		showExport,
		showReaderNotes,
		showReadingProgress,
		showBookmark,
	} = attributes;

	const blocks = useSelect(
		( select ) => select( blockEditorStore ).getBlocks(),
		[]
	);

	const levels = [
		showH1 && 1,
		showH2 && 2,
		showH3 && 3,
		showH4 && 4,
		showH5 && 5,
		showH6 && 6,
	].filter( Boolean );

	// All headings — used for the Section Notes panel.
	const allHeadings = collectHeadings( blocks );

	const items = filterAndNormalize(
		allHeadings,
		levels.length ? levels : [ 2 ]
	);

	const editorConfig =
		typeof window !== 'undefined' && window.tocguideEditor
			? window.tocguideEditor
			: {};
	const markerStyle = editorConfig.markerStyle || '';
	const shadow = editorConfig.shadow || '';
	let isolateTheme = editorConfig.excludeThemeStyles !== false;
	if ( excludeThemeStyles === 'yes' ) {
		isolateTheme = true;
	} else if ( excludeThemeStyles === 'no' ) {
		isolateTheme = false;
	}

	const style = { ...( editorConfig.designVars || {} ) };
	if ( scrollOffset >= 0 ) {
		style[ '--tocguide-offset' ] = `${ scrollOffset }px`;
	}
	if ( maxHeight > 0 ) {
		style[ '--tocguide-max-height' ] = `${ maxHeight }px`;
	}

	const styleMatch = ( attributes.className || '' ).match(
		/(?:^|\s)is-style-([a-z0-9-]+)/
	);
	const styleSlug = styleMatch
		? styleMatch[ 1 ]
		: attributes.stylePreset || '';
	const legacyStyle =
		! styleMatch &&
		[ 'minimal', 'boxed', 'underline', 'card' ].includes( styleSlug )
			? `is-style-${ styleSlug } tocguide--${ styleSlug }`
			: '';

	const blockProps = useBlockProps( {
		className: [
			'tocguide',
			legacyStyle,
			isolateTheme ? 'is-theme-isolated' : '',
			markerStyle === 'square' ? 'is-marker-square' : '',
			markerStyle === 'plain' ? 'is-marker-plain' : '',
			shadow === 'soft' ? 'has-shadow-soft' : '',
			shadow === 'medium' ? 'has-shadow-medium' : '',
			sticky ? 'is-sticky' : '',
			fixedLeft ? 'is-fixed-left' : '',
			collapsible ? 'is-collapsible' : '',
			compact ? 'is-compact' : '',
			hideMarkers ? 'is-no-markers' : '',
			twoColumns ? 'has-columns-2' : '',
			underlineLinks ? 'has-underlined-links' : '',
			ordered && numbering === 'nested' ? 'is-nested-counters' : '',
			maxHeight > 0 ? 'has-max-height' : '',
		]
			.filter( Boolean )
			.join( ' ' ),
		style: Object.keys( style ).length ? style : undefined,
		'aria-label': title || __( 'Table of Contents', 'tocguide' ),
	} );

	const TitleTag = [ 'h2', 'h3', 'h4' ].includes( titleTag ) ? titleTag : 'p';

	return (
		<>
			<BlockControls group="block">
				<ToolbarGroup>
					<ToolbarButton
						icon={ formatListBullets }
						label={ __( 'Bulleted list', 'tocguide' ) }
						isPressed={ ! ordered }
						onClick={ () =>
							setAttributes( {
								ordered: false,
								numbering: 'default',
							} )
						}
					/>
					<ToolbarButton
						icon={ formatListNumbered }
						label={ __( 'Numbered list', 'tocguide' ) }
						isPressed={ ordered }
						onClick={ () => setAttributes( { ordered: true } ) }
					/>
				</ToolbarGroup>
			</BlockControls>

			<InspectorControls>
				<PanelBody title={ __( 'Heading levels', 'tocguide' ) }>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Include H1 headings', 'tocguide' ) }
						checked={ showH1 }
						onChange={ ( value ) =>
							setAttributes( { showH1: value } )
						}
						help={ __(
							'Most themes already print the post title as H1. Only enable this if headings inside the content use H1.',
							'tocguide'
						) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Include H2 headings', 'tocguide' ) }
						checked={ showH2 }
						onChange={ ( value ) =>
							setAttributes( { showH2: value } )
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Include H3 headings', 'tocguide' ) }
						checked={ showH3 }
						onChange={ ( value ) =>
							setAttributes( { showH3: value } )
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Include H4 headings', 'tocguide' ) }
						checked={ showH4 }
						onChange={ ( value ) =>
							setAttributes( { showH4: value } )
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Include H5 headings', 'tocguide' ) }
						checked={ showH5 }
						onChange={ ( value ) =>
							setAttributes( { showH5: value } )
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Include H6 headings', 'tocguide' ) }
						checked={ showH6 }
						onChange={ ( value ) =>
							setAttributes( { showH6: value } )
						}
					/>
				</PanelBody>
				<PanelBody
					title={ __( 'Title', 'tocguide' ) }
					initialOpen={ false }
				>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show title', 'tocguide' ) }
						checked={ showTitle }
						onChange={ ( value ) =>
							setAttributes( { showTitle: value } )
						}
						help={ __(
							'The title still appears in the editor so you can edit the accessible name.',
							'tocguide'
						) }
					/>
					<SelectControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Title element', 'tocguide' ) }
						value={ titleTag }
						options={ [
							{
								label: __( 'Paragraph', 'tocguide' ),
								value: 'p',
							},
							{ label: 'H2', value: 'h2' },
							{ label: 'H3', value: 'h3' },
							{ label: 'H4', value: 'h4' },
						] }
						onChange={ ( value ) =>
							setAttributes( { titleTag: value } )
						}
						help={ __(
							'Use a heading if this outline should appear in the document outline. Prefer a paragraph when the post already has a nearby heading.',
							'tocguide'
						) }
					/>
				</PanelBody>
				<PanelBody
					title={ __( 'List & layout', 'tocguide' ) }
					initialOpen={ false }
				>
					<SelectControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Theme styles', 'tocguide' ) }
						value={ excludeThemeStyles || 'inherit' }
						options={ [
							{
								label: __( 'Use the site setting', 'tocguide' ),
								value: 'inherit',
							},
							{
								label: __( 'Exclude theme styles', 'tocguide' ),
								value: 'yes',
							},
							{
								label: __(
									'Inherit theme list styles',
									'tocguide'
								),
								value: 'no',
							},
						] }
						onChange={ ( value ) =>
							setAttributes( { excludeThemeStyles: value } )
						}
						help={ __(
							'Exclude theme styles removes the theme’s list numbers, including a stray “0.”, and uses TOCguide badges instead. Fonts, colours, and shadow are set under Settings → TOCguide → Design.',
							'tocguide'
						) }
					/>
					{ ordered && (
						<SelectControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __( 'Numbering', 'tocguide' ) }
							value={ numbering }
							options={ [
								{
									label: __(
										'Sequential (1, 2, 3)',
										'tocguide'
									),
									value: 'default',
								},
								{
									label: __(
										'Nested (1, 1.1, 1.1.1)',
										'tocguide'
									),
									value: 'nested',
								},
							] }
							onChange={ ( value ) =>
								setAttributes( { numbering: value } )
							}
						/>
					) }
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Hide bullets and numbers', 'tocguide' ) }
						checked={ hideMarkers }
						onChange={ ( value ) =>
							setAttributes( { hideMarkers: value } )
						}
						help={
							ordered && numbering === 'nested'
								? __(
										'Nested numbering still prints 1.1-style counters.',
										'tocguide'
								  )
								: undefined
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Two columns', 'tocguide' ) }
						checked={ twoColumns }
						onChange={ ( value ) =>
							setAttributes( { twoColumns: value } )
						}
						help={ __(
							'Top-level items sit side by side. Stacks on small screens.',
							'tocguide'
						) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Compact spacing', 'tocguide' ) }
						checked={ compact }
						onChange={ ( value ) =>
							setAttributes( { compact: value } )
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Always underline links', 'tocguide' ) }
						checked={ underlineLinks }
						onChange={ ( value ) =>
							setAttributes( { underlineLinks: value } )
						}
					/>
					<RangeControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Max height (px)', 'tocguide' ) }
						value={ maxHeight }
						min={ 0 }
						max={ 800 }
						step={ 40 }
						onChange={ ( value ) =>
							setAttributes( { maxHeight: value } )
						}
						help={ __(
							'0 is unlimited. A max height makes long outlines scroll — useful with sticky.',
							'tocguide'
						) }
					/>
				</PanelBody>
				<PanelBody
					title={ __( 'Behavior', 'tocguide' ) }
					initialOpen={ false }
				>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Sticky while scrolling', 'tocguide' ) }
						checked={ sticky }
						onChange={ ( value ) =>
							setAttributes( { sticky: value } )
						}
						help={ __(
							'Keeps the outline in view in a sidebar or wide column.',
							'tocguide'
						) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Fixed left', 'tocguide' ) }
						checked={ !! fixedLeft }
						onChange={ ( value ) =>
							setAttributes( { fixedLeft: value } )
						}
						help={ __(
							'On wide screens, pins this outline to the left edge. Settings → Position → Fixed left does this for every outline.',
							'tocguide'
						) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Collapsible', 'tocguide' ) }
						checked={ collapsible }
						onChange={ ( value ) =>
							setAttributes( { collapsible: value } )
						}
					/>
					{ collapsible && (
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __( 'Start collapsed', 'tocguide' ) }
							checked={ collapsedDefault }
							onChange={ ( value ) =>
								setAttributes( { collapsedDefault: value } )
							}
						/>
					) }
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Close button', 'tocguide' ) }
						checked={ showClose !== false }
						onChange={ ( value ) =>
							setAttributes( { showClose: value } )
						}
						help={ __(
							'Readers can hide the outline. Show outline brings it back for that visit.',
							'tocguide'
						) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Focused reading', 'tocguide' ) }
						checked={ !! focusMode }
						onChange={ ( value ) =>
							setAttributes( { focusMode: value } )
						}
						help={ __(
							'Adds a Focus control. Turning it on clears the page so only the post copy remains, on a plain sheet.',
							'tocguide'
						) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __(
							'Highlight the section in view',
							'tocguide'
						) }
						checked={ highlightActive }
						onChange={ ( value ) =>
							setAttributes( { highlightActive: value } )
						}
					/>
					<SelectControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Smooth scroll', 'tocguide' ) }
						value={ smoothScroll }
						options={ [
							{
								label: __( 'Use site setting', 'tocguide' ),
								value: 'inherit',
							},
							{
								label: __( 'On', 'tocguide' ),
								value: 'on',
							},
							{
								label: __( 'Off', 'tocguide' ),
								value: 'off',
							},
						] }
						onChange={ ( value ) =>
							setAttributes( { smoothScroll: value } )
						}
					/>
					<RangeControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Minimum headings override', 'tocguide' ) }
						value={ minHeadings }
						min={ -1 }
						max={ 10 }
						onChange={ ( value ) =>
							setAttributes( { minHeadings: value } )
						}
						help={ __(
							'Use −1 to inherit Settings → TOCguide. Hide this block when the post has fewer matching headings.',
							'tocguide'
						) }
					/>
					<RangeControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __(
							'Scroll offset override (px)',
							'tocguide'
						) }
						value={ scrollOffset }
						min={ -1 }
						max={ 400 }
						onChange={ ( value ) =>
							setAttributes( { scrollOffset: value } )
						}
						help={ __(
							'Use −1 to inherit the site-wide offset from Settings → TOCguide.',
							'tocguide'
						) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Export / print bar', 'tocguide' ) }
						checked={ showExport }
						onChange={ ( value ) =>
							setAttributes( { showExport: value } )
						}
						help={ __(
							'Adds Copy, Download (.md), Download (.doc), and Print buttons below the outline. Useful for writers and researchers.',
							'tocguide'
						) }
					/>
				</PanelBody>

				{ /* ── Study Tools ──────────────────────────────────────── */ }
				<PanelBody
					title={ __( 'Study Tools', 'tocguide' ) }
					initialOpen={ false }
				>
					<p className="components-base-control__help">
						{ __(
							"Reader-facing tools — stored locally in each visitor's browser. No account or server calls required.",
							'tocguide'
						) }
					</p>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Reading progress bar', 'tocguide' ) }
						checked={ showReadingProgress }
						onChange={ ( value ) =>
							setAttributes( { showReadingProgress: value } )
						}
						help={ __(
							'A thin bar shows readers how far through the article they are (0–100 %).',
							'tocguide'
						) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Resume reading bookmark', 'tocguide' ) }
						checked={ showBookmark }
						onChange={ ( value ) =>
							setAttributes( { showBookmark: value } )
						}
						help={ __(
							'Remembers the reader\'s last position. A "Resume" button appears on their next visit to jump back.',
							'tocguide'
						) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Reader note pads', 'tocguide' ) }
						checked={ showReaderNotes }
						onChange={ ( value ) =>
							setAttributes( { showReaderNotes: value } )
						}
						help={ __(
							'Readers can jot personal notes per section (📝). Notes are saved privately in their browser — great for research and study.',
							'tocguide'
						) }
					/>
				</PanelBody>

				{ /* ── Reading Guide ───────────────────────────────────── */ }
				<PanelBody
					title={ __( 'Reading Guide', 'tocguide' ) }
					initialOpen={ false }
				>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Hover section preview', 'tocguide' ) }
						checked={ previewOnHover }
						onChange={ ( value ) =>
							setAttributes( { previewOnHover: value } )
						}
						help={ __(
							'Show the opening sentence of each section in a floating tooltip when hovering over its TOC link. Works on its own — no need to enable the full Reading Guide.',
							'tocguide'
						) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Full Reading Guide', 'tocguide' ) }
						checked={ guideMode }
						onChange={ ( value ) =>
							setAttributes( { guideMode: value } )
						}
						help={ __(
							'Adds inline section previews, read-time estimates, density bars, reading progress, reactions, and per-section citations.',
							'tocguide'
						) }
					/>
					{ guideMode && (
						<>
							<ToggleControl
								__nextHasNoMarginBottom
								label={ __( 'Section previews', 'tocguide' ) }
								checked={ showPreviews }
								onChange={ ( value ) =>
									setAttributes( { showPreviews: value } )
								}
								help={ __(
									'Show the opening sentence of each section beneath its TOC link.',
									'tocguide'
								) }
							/>
							<ToggleControl
								__nextHasNoMarginBottom
								label={ __(
									'Section length bars',
									'tocguide'
								) }
								checked={ showDensity }
								onChange={ ( value ) =>
									setAttributes( { showDensity: value } )
								}
								help={ __(
									'Thin bar showing relative word count — readers see which sections are short vs long at a glance.',
									'tocguide'
								) }
							/>
							<ToggleControl
								__nextHasNoMarginBottom
								label={ __(
									'Read-time estimates',
									'tocguide'
								) }
								checked={ showReadTime }
								onChange={ ( value ) =>
									setAttributes( { showReadTime: value } )
								}
								help={ __(
									'Show ~N min alongside each section link.',
									'tocguide'
								) }
							/>
							<ToggleControl
								__nextHasNoMarginBottom
								label={ __( 'Reading progress', 'tocguide' ) }
								checked={ trackProgress }
								onChange={ ( value ) =>
									setAttributes( { trackProgress: value } )
								}
								help={ __(
									'Fade sections as the reader scrolls past them.',
									'tocguide'
								) }
							/>
							<ToggleControl
								__nextHasNoMarginBottom
								label={ __( 'Emoji reactions', 'tocguide' ) }
								checked={ showReactions }
								onChange={ ( value ) =>
									setAttributes( { showReactions: value } )
								}
								help={ __(
									'Readers react per section (💡 ⭐ 🤔 ✅). Stored in their browser — no account needed.',
									'tocguide'
								) }
							/>
							<ToggleControl
								__nextHasNoMarginBottom
								label={ __(
									'Per-section citations',
									'tocguide'
								) }
								checked={ showCitations }
								onChange={ ( value ) =>
									setAttributes( { showCitations: value } )
								}
								help={ __(
									'One-click copy of a formatted academic citation (APA, MLA, Chicago, etc.) for any section. Perfect for research content.',
									'tocguide'
								) }
							/>
							{ showCitations && (
								<SelectControl
									__nextHasNoMarginBottom
									__next40pxDefaultSize
									label={ __(
										'Citation format',
										'tocguide'
									) }
									value={ citationStyle }
									options={ [
										{ label: 'APA', value: 'apa' },
										{ label: 'MLA', value: 'mla' },
										{
											label: 'Chicago',
											value: 'chicago',
										},
										{
											label: 'Harvard',
											value: 'harvard',
										},
										{
											label: __(
												'Plain link',
												'tocguide'
											),
											value: 'plain',
										},
									] }
									onChange={ ( value ) =>
										setAttributes( {
											citationStyle: value,
										} )
									}
								/>
							) }
						</>
					) }
				</PanelBody>

				{ /* ── Section Planner (author tools) ────────────────── */ }
				<PanelBody
					title={ __( 'Section Planner', 'tocguide' ) }
					initialOpen={ false }
				>
					<p className="components-base-control__help">
						{ __(
							'Track writing status per section and add reader-facing teasers (shown in Reading Guide mode).',
							'tocguide'
						) }
					</p>
					{ allHeadings.length === 0 && (
						<p>
							{ __(
								'Add Heading blocks to this post and they will appear here.',
								'tocguide'
							) }
						</p>
					) }
					{ allHeadings.map( ( heading ) => (
						<div
							key={ heading.slug }
							style={ { marginBottom: '1rem' } }
						>
							<SelectControl
								__nextHasNoMarginBottom
								__next40pxDefaultSize
								label={ heading.text }
								value={ sectionStatus[ heading.slug ] || '' }
								options={ [
									{
										label: __(
											'— No status —',
											'tocguide'
										),
										value: '',
									},
									{
										label: __( '✏️ Draft', 'tocguide' ),
										value: 'draft',
									},
									{
										label: __(
											'🔄 In progress',
											'tocguide'
										),
										value: 'progress',
									},
									{
										label: __( '✅ Done', 'tocguide' ),
										value: 'done',
									},
								] }
								onChange={ ( value ) => {
									const updated = { ...sectionStatus };
									if ( value ) {
										updated[ heading.slug ] = value;
									} else {
										delete updated[ heading.slug ];
									}
									setAttributes( {
										sectionStatus: updated,
									} );
								} }
							/>
							<TextareaControl
								label={ __(
									'Reader teaser (optional)',
									'tocguide'
								) }
								value={ sectionNotes[ heading.slug ] || '' }
								onChange={ ( value ) => {
									const updated = { ...sectionNotes };
									if ( value ) {
										updated[ heading.slug ] = value;
									} else {
										delete updated[ heading.slug ];
									}
									setAttributes( { sectionNotes: updated } );
								} }
								rows={ 2 }
								__nextHasNoMarginBottom
								placeholder={ __(
									'Teaser or hook for this section…',
									'tocguide'
								) }
							/>
						</div>
					) ) }
				</PanelBody>
			</InspectorControls>

			<nav { ...blockProps }>
				<div className="tocguide__header">
					<RichText
						tagName={ TitleTag }
						className="tocguide__title"
						identifier="title"
						value={ title }
						onChange={ ( value ) =>
							setAttributes( { title: value } )
						}
						placeholder={ __( 'Table of Contents', 'tocguide' ) }
						allowedFormats={ [] }
						withoutInteractiveFormatting
						style={ showTitle ? undefined : { opacity: 0.45 } }
					/>
					<div
						className="tocguide__header-actions"
						aria-hidden="true"
					>
						{ !! focusMode && (
							<span className="tocguide__focus">
								<FocusGlyph />
								<span>{ __( 'Focus', 'tocguide' ) }</span>
							</span>
						) }
						{ showClose !== false && (
							<span className="tocguide__close">
								<CloseGlyph />
							</span>
						) }
					</div>
				</div>
				<Disabled>
					<div className="tocguide__body">
						{ items.length ? (
							<PreviewList
								items={ items }
								ordered={ ordered }
								numbering={ numbering }
								hideMarkers={ hideMarkers }
								showReaderNotes={ showReaderNotes }
								isolate={ isolateTheme }
								sectionStatus={ sectionStatus }
							/>
						) : (
							<p className="tocguide__placeholder">
								{ __(
									'Add Heading blocks to this post and they will appear here. Headings with the class no-toc are skipped.',
									'tocguide'
								) }
							</p>
						) }
					</div>
				</Disabled>
			</nav>
		</>
	);
}
