/**
 * Front-end behavior for the Table of Contents block.
 *
 * Loaded via block.json `viewScript`. wp-scripts extracts `@wordpress/*`
 * imports as script dependencies (no jQuery, no IIFE wrapper).
 *
 * Reading Guide features (progress tracking, reactions, author notes,
 * per-section citations) activate only when the nav carries `has-guide-mode`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script
 */
import domReady from '@wordpress/dom-ready';
import { __ } from '@wordpress/i18n';

const prefersReduced = () =>
	window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

const offsetOf = ( nav ) =>
	parseInt( nav.getAttribute( 'data-tocguide-offset' ) || '0', 10 ) || 0;

const smoothEnabled = ( nav ) =>
	nav.getAttribute( 'data-tocguide-smooth' ) !== '0';

/**
 * Announce a short message to screen readers via the nav's live region.
 *
 * @param {HTMLElement} nav     The TOC nav element.
 * @param {string}      message Text to announce.
 */
const announce = ( nav, message ) => {
	const region = nav.querySelector( '.tocguide__live-region' );
	if ( ! region ) {
		return;
	}
	// Clear first so the same text re-announces if repeated.
	region.textContent = '';
	// Allow the DOM to settle before setting the new text.
	window.requestAnimationFrame( () => {
		region.textContent = message;
	} );
};

// ── Collapse toggle ───────────────────────────────────────────────────────────

const initToggle = ( nav ) => {
	const button = nav.querySelector( '.tocguide__toggle' );
	if ( ! button ) {
		return;
	}
	button.addEventListener( 'click', () => {
		const expanded = button.getAttribute( 'aria-expanded' ) === 'true';
		button.setAttribute( 'aria-expanded', expanded ? 'false' : 'true' );
		nav.classList.toggle( 'is-collapsed', expanded );
	} );
};

// ── Close (hide the outline for this visit) ──────────────────────────────────

const visitKey = ( nav, kind ) => {
	const post =
		nav.getAttribute( 'data-tocguide-post' ) || window.location.pathname;
	const instance = nav.dataset.tocguideInstance || '0';
	return `tocguide-${ kind }:${ post }:${ instance }`;
};

const remember = ( key, value ) => {
	try {
		if ( value ) {
			window.sessionStorage.setItem( key, '1' );
		} else {
			window.sessionStorage.removeItem( key );
		}
	} catch {
		// sessionStorage may be blocked.
	}
};

const remembered = ( key ) => {
	try {
		return window.sessionStorage.getItem( key ) === '1';
	} catch {
		return false;
	}
};

// Desktop only, on a single post or page: keep the outline in a left
// column of <main>. The page canvas (header, footer, body padding) stays put.
// Archives print the same block inside each post, so they are left alone.
let dockNav = null;
let dockQuery = null;
let dockHost = null;
let dockPlaceholder = null;
let resumeHome = null;

const clearDock = () => {
	document.documentElement.classList.remove( 'tocguide-has-dock' );
	document.documentElement.style.removeProperty( '--tocguide-dock-offset' );
	if ( dockNav ) {
		const resume = dockNav.querySelector( '.tocguide__resume-btn' );
		if (
			resume &&
			resumeHome &&
			resumeHome.parent &&
			resume.parentElement !== resumeHome.parent
		) {
			resumeHome.parent.insertBefore( resume, resumeHome.next );
		}
		dockNav.classList.remove( 'is-docked' );
		if ( dockPlaceholder && dockPlaceholder.parentNode ) {
			dockPlaceholder.parentNode.insertBefore( dockNav, dockPlaceholder );
			dockPlaceholder.remove();
		}
	}
	dockPlaceholder = null;
	resumeHome = null;
	dockNav = null;
	if ( dockHost ) {
		dockHost.classList.remove( 'tocguide-dock-host' );
		dockHost = null;
	}
};

const dockMedia = () => {
	if ( ! dockQuery && window.matchMedia ) {
		dockQuery = window.matchMedia( '(min-width: 1100px)' );
		const onChange = () => applyDock();
		if ( dockQuery.addEventListener ) {
			dockQuery.addEventListener( 'change', onChange );
		} else if ( dockQuery.addListener ) {
			dockQuery.addListener( onChange );
		}
	}
	return dockQuery;
};

// `single`, `page`, and `attachment` are the body classes WordPress has
// used for is_singular() for years. `wp-singular` is the newer alias.
const isSingularView = () => {
	const classes = document.body.classList;
	return (
		classes.contains( 'wp-singular' ) ||
		classes.contains( 'single' ) ||
		classes.contains( 'page' ) ||
		classes.contains( 'attachment' )
	);
};

const canDock = () => {
	if ( ! document.body || document.body.classList.contains( 'wp-admin' ) ) {
		return false;
	}
	if ( ! isSingularView() ) {
		return false;
	}
	if ( document.querySelector( '.editor-styles-wrapper' ) ) {
		return false;
	}
	const query = dockMedia();
	return Boolean( query && query.matches );
};

const dockHostOf = ( nav ) =>
	nav.closest( 'main' ) ||
	document.querySelector( 'main' ) ||
	nav.closest( 'article' );

const placeInHost = ( nav, host ) => {
	if ( nav.parentElement === host ) {
		return;
	}
	if ( ! dockPlaceholder ) {
		dockPlaceholder = document.createComment( 'tocguide-dock' );
	}
	if ( ! dockPlaceholder.parentNode && nav.parentNode ) {
		nav.parentNode.insertBefore( dockPlaceholder, nav );
	}
	host.insertBefore( nav, host.firstChild );
};

const lineUpDockButtons = ( nav ) => {
	const actions = nav.querySelector( '.tocguide__header-actions' );
	const resume = nav.querySelector( '.tocguide__resume-btn' );
	if ( ! actions || ! resume || resume.parentElement === actions ) {
		return;
	}
	resumeHome = {
		parent: resume.parentElement,
		next: resume.nextSibling,
	};
	actions.appendChild( resume );
};

const applyDock = () => {
	const first = document.querySelector(
		'.wp-block-tocguide-table-of-contents.is-fixed-left, .tocguide.is-fixed-left'
	);
	const host = first ? dockHostOf( first ) : null;
	if ( ! canDock() || ! first || ! host ) {
		clearDock();
		return;
	}
	if ( dockNav && dockNav !== first ) {
		clearDock();
	}
	dockNav = first;
	dockHost = host;
	placeInHost( first, host );
	lineUpDockButtons( first );
	first.classList.add( 'is-docked' );
	host.classList.add( 'tocguide-dock-host' );
	document.documentElement.classList.add( 'tocguide-has-dock' );
};

const initClose = ( nav ) => {
	const button = nav.querySelector( '.tocguide__close' );
	const restore = nav.querySelector( '.tocguide__restore' );
	if ( ! button || ! restore ) {
		return;
	}
	const key = visitKey( nav, 'closed' );

	const dismiss = ( persist ) => {
		nav.classList.add( 'is-dismissed' );
		restore.removeAttribute( 'hidden' );
		if ( persist ) {
			remember( key, true );
			announce( nav, 'Table of contents hidden.' );
			restore.focus();
		}
	};

	const show = () => {
		nav.classList.remove( 'is-dismissed' );
		restore.setAttribute( 'hidden', '' );
		remember( key, false );
		announce( nav, 'Table of contents shown.' );
		button.focus();
	};

	if ( remembered( key ) ) {
		dismiss( false );
	}

	button.addEventListener( 'click', () => dismiss( true ) );
	restore.addEventListener( 'click', show );
};

// ── Focused reading (post copy only, on a plain sheet) ───────────────────────

let focusNav = null;
let focusFrame = 0;
let focusListening = false;
let stopFocus = null;

const FOCUS_EXIT_ID = 'tocguide-focus-exit';

const trackedHeadings = ( nav ) =>
	Array.from( nav.querySelectorAll( '.tocguide__link[href^="#"]' ) )
		.map( ( link ) => {
			const id = decodeURIComponent(
				( link.getAttribute( 'href' ) || '' ).slice( 1 )
			);
			return id ? document.getElementById( id ) : null;
		} )
		.filter( Boolean );

const contentRootOf = ( headings ) => {
	const selectors = [
		'.entry-content',
		'.wp-block-post-content',
		'.post-content',
		'article',
		'main',
	];
	for ( let i = 0; i < selectors.length; i++ ) {
		const roots = document.querySelectorAll( selectors[ i ] );
		for ( let r = 0; r < roots.length; r++ ) {
			const root = roots[ r ];
			if ( headings.every( ( heading ) => root.contains( heading ) ) ) {
				return root;
			}
		}
	}
	return headings[ 0 ] ? headings[ 0 ].parentElement : null;
};

const clearFocusMarks = () => {
	document
		.querySelectorAll( '.tocguide-focus-hide, .tocguide-focus-paper' )
		.forEach( ( el ) => {
			el.classList.remove(
				'tocguide-focus-hide',
				'tocguide-focus-paper'
			);
		} );
};

const isFocusSkippable = ( node ) => {
	if ( ! node || node.nodeType !== 1 ) {
		return true;
	}
	const tag = node.tagName;
	return (
		tag === 'SCRIPT' ||
		tag === 'STYLE' ||
		tag === 'LINK' ||
		tag === 'NOSCRIPT'
	);
};

const isFocusControlTree = ( node ) => {
	if ( ! node || node.nodeType !== 1 ) {
		return false;
	}
	if ( node.id === FOCUS_EXIT_ID ) {
		return true;
	}
	return Boolean(
		node.classList.contains( 'wp-block-tocguide-table-of-contents' ) ||
			node.classList.contains( 'tocguide' ) ||
			node.classList.contains( 'tocguide__focus' ) ||
			node.querySelector(
				'.wp-block-tocguide-table-of-contents, .tocguide__focus, #' +
					FOCUS_EXIT_ID
			)
	);
};

const paperRoot = ( nav ) => {
	const headings = trackedHeadings( nav );
	if ( headings.length ) {
		const root = contentRootOf( headings );
		if ( root ) {
			return root;
		}
	}
	return document.querySelector(
		'.entry-content, .wp-block-post-content, .post-content, article, main'
	);
};

const paintFocus = () => {
	clearFocusMarks();
	if (
		! focusNav ||
		! document.documentElement.classList.contains( 'tocguide-is-focusing' )
	) {
		return null;
	}
	const paper = paperRoot( focusNav );
	if ( ! paper ) {
		return null;
	}
	paper.classList.add( 'tocguide-focus-paper' );
	let node = paper;
	while ( node && node !== document.body && node.parentElement ) {
		Array.from( node.parentElement.children ).forEach( ( sibling ) => {
			if (
				sibling !== node &&
				! isFocusSkippable( sibling ) &&
				! isFocusControlTree( sibling )
			) {
				sibling.classList.add( 'tocguide-focus-hide' );
			}
		} );
		node = node.parentElement;
	}
	return paper;
};

const scheduleFocusPaint = () => {
	if ( focusFrame ) {
		return;
	}
	focusFrame = window.requestAnimationFrame( () => {
		focusFrame = 0;
		paintFocus();
	} );
};

const listenForFocusScroll = () => {
	if ( focusListening ) {
		return;
	}
	focusListening = true;
	window.addEventListener( 'scroll', scheduleFocusPaint, { passive: true } );
	window.addEventListener( 'resize', scheduleFocusPaint );
	document.addEventListener( 'keydown', onFocusKey );
};

const focusRestoreText = ( button ) =>
	button.getAttribute( 'data-tocguide-restore' ) ||
	__( 'Show page', 'tocguide' );

const appendFocusExitGlyph = ( host ) => {
	const svg = document.createElementNS( 'http://www.w3.org/2000/svg', 'svg' );
	svg.setAttribute( 'class', 'tocguide__svg' );
	svg.setAttribute( 'viewBox', '0 0 24 24' );
	svg.setAttribute( 'width', '16' );
	svg.setAttribute( 'height', '16' );
	svg.setAttribute( 'fill', 'none' );
	svg.setAttribute( 'aria-hidden', 'true' );
	svg.setAttribute( 'focusable', 'false' );
	[
		'M8.25 3.5H5.5A2 2 0 0 0 3.5 5.5v2.75',
		'M15.75 3.5H18.5A2 2 0 0 1 20.5 5.5v2.75',
		'M8.25 20.5H5.5A2 2 0 0 1 3.5 18.5v-2.75',
		'M15.75 20.5H18.5A2 2 0 0 0 20.5 18.5v-2.75',
	].forEach( ( d ) => {
		const path = document.createElementNS(
			'http://www.w3.org/2000/svg',
			'path'
		);
		path.setAttribute( 'd', d );
		path.setAttribute( 'stroke', 'currentColor' );
		path.setAttribute( 'stroke-width', '1.6' );
		path.setAttribute( 'stroke-linecap', 'round' );
		path.setAttribute( 'stroke-linejoin', 'round' );
		svg.appendChild( path );
	} );
	host.appendChild( svg );
};

const fillFocusExit = ( exit, restore ) => {
	if ( exit.dataset.tocguideRestore === restore && exit.childElementCount ) {
		return;
	}
	exit.dataset.tocguideRestore = restore;
	exit.replaceChildren();

	const icon = document.createElement( 'span' );
	icon.className = 'tocguide__focus-exit-icon';
	icon.setAttribute( 'aria-hidden', 'true' );
	appendFocusExitGlyph( icon );

	const copy = document.createElement( 'span' );
	copy.className = 'tocguide__focus-exit-copy';
	const label = document.createElement( 'span' );
	label.className = 'tocguide__focus-exit-label';
	label.textContent = restore;
	const detail = document.createElement( 'span' );
	detail.className = 'tocguide__focus-exit-detail';
	detail.textContent = __( 'Bring the rest back', 'tocguide' );
	copy.append( label, detail );

	const hint = document.createElement( 'span' );
	hint.className = 'tocguide__focus-exit-kbd';
	hint.setAttribute( 'aria-hidden', 'true' );
	hint.textContent = 'Esc';

	exit.append( icon, copy, hint );
	exit.setAttribute( 'aria-label', restore );
	exit.setAttribute( 'aria-keyshortcuts', 'Escape' );
};

const ensureFocusExit = () => {
	let exit = document.getElementById( FOCUS_EXIT_ID );
	if ( exit ) {
		return exit;
	}
	exit = document.createElement( 'button' );
	exit.type = 'button';
	exit.id = FOCUS_EXIT_ID;
	exit.className = 'tocguide__focus-exit';
	exit.hidden = true;
	document.body.appendChild( exit );
	exit.addEventListener( 'click', () => {
		if ( typeof stopFocus === 'function' ) {
			stopFocus( true );
		}
	} );
	return exit;
};

const syncFocusChrome = ( on ) => {
	document
		.querySelectorAll( '.tocguide__focus:not(.tocguide__focus-exit)' )
		.forEach( ( btn ) => {
			if ( ! btn.dataset.tocguideFocusLabel ) {
				btn.dataset.tocguideFocusLabel =
					btn.getAttribute( 'aria-label' ) ||
					__( 'Focused reading', 'tocguide' );
			}
			if ( ! btn.dataset.tocguideFocusText ) {
				const span = btn.querySelector( 'span' );
				btn.dataset.tocguideFocusText = span
					? span.textContent
					: __( 'Focus', 'tocguide' );
			}
			const restore = focusRestoreText( btn );
			btn.setAttribute( 'aria-pressed', on ? 'true' : 'false' );
			btn.setAttribute(
				'aria-label',
				on ? restore : btn.dataset.tocguideFocusLabel
			);
			const label = btn.querySelector( 'span' );
			if ( label ) {
				label.textContent = on
					? restore
					: btn.dataset.tocguideFocusText;
			}
		} );

	if ( ! on ) {
		const exit = document.getElementById( FOCUS_EXIT_ID );
		if ( exit ) {
			exit.hidden = true;
		}
		return;
	}
	const source = document.querySelector(
		'.tocguide__focus:not(.tocguide__focus-exit)'
	);
	const restore = source
		? focusRestoreText( source )
		: __( 'Show page', 'tocguide' );
	const exit = ensureFocusExit();
	fillFocusExit( exit, restore );
	exit.hidden = false;
};

const onFocusKey = ( event ) => {
	if ( event.key !== 'Escape' ) {
		return;
	}
	if (
		! document.documentElement.classList.contains( 'tocguide-is-focusing' )
	) {
		return;
	}
	event.preventDefault();
	if ( typeof stopFocus === 'function' ) {
		stopFocus( true );
	}
};

const initFocus = ( nav ) => {
	const button = nav.querySelector( '.tocguide__focus' );
	if ( ! button ) {
		return;
	}
	const key = visitKey( nav, 'focus' );

	const enable = ( persist ) => {
		focusNav = nav;
		stopFocus = disable;
		document.documentElement.classList.add( 'tocguide-is-focusing' );
		syncFocusChrome( true );
		listenForFocusScroll();
		if ( persist ) {
			remember( key, true );
			announce( nav, __( 'Focused reading on.', 'tocguide' ) );
		}
		const paper = paintFocus();
		if ( persist && paper && paper.scrollIntoView ) {
			paper.scrollIntoView( {
				block: 'start',
				behavior: prefersReduced() ? 'auto' : 'smooth',
			} );
		}
	};

	const disable = ( persist ) => {
		document.documentElement.classList.remove( 'tocguide-is-focusing' );
		syncFocusChrome( false );
		clearFocusMarks();
		if ( focusNav === nav ) {
			focusNav = null;
		}
		if ( persist ) {
			remember( key, false );
			announce( nav, __( 'Focused reading off.', 'tocguide' ) );
		}
	};

	button.addEventListener( 'click', () => {
		const on = button.getAttribute( 'aria-pressed' ) === 'true';
		if ( on ) {
			disable( true );
		} else {
			enable( true );
		}
	} );

	if ( remembered( key ) && ! focusNav ) {
		enable( false );
	}
};

// ── Smooth scroll ─────────────────────────────────────────────────────────────

const initSmoothScroll = ( nav ) => {
	nav.addEventListener( 'click', ( event ) => {
		const link = event.target.closest( 'a[href^="#"]' );
		if ( ! link || ! nav.contains( link ) ) {
			return;
		}
		const id = decodeURIComponent(
			( link.getAttribute( 'href' ) || '' ).slice( 1 )
		);
		if ( ! id ) {
			return;
		}
		const target = document.getElementById( id );
		if ( ! target ) {
			return;
		}
		event.preventDefault();
		const top =
			target.getBoundingClientRect().top +
			window.scrollY -
			offsetOf( nav );
		const behavior =
			smoothEnabled( nav ) && ! prefersReduced() ? 'smooth' : 'auto';
		window.scrollTo( { top: Math.max( 0, top ), behavior } );
		if ( window.history.pushState ) {
			window.history.pushState( null, '', `#${ id }` );
		}
		target.setAttribute( 'tabindex', '-1' );
		target.focus( { preventScroll: true } );
	} );
};

// ── Scroll spy (highlight active section) ────────────────────────────────────

const initScrollSpy = ( nav ) => {
	if ( ! nav.classList.contains( 'has-scroll-spy' ) ) {
		return;
	}
	const links = Array.from(
		nav.querySelectorAll( '.tocguide__link[href^="#"]' )
	);
	if ( ! links.length ) {
		return;
	}

	const map = links
		.map( ( link ) => {
			const id = decodeURIComponent(
				( link.getAttribute( 'href' ) || '' ).slice( 1 )
			);
			const heading = id ? document.getElementById( id ) : null;
			return heading ? { link, heading } : null;
		} )
		.filter( Boolean );

	if ( ! map.length ) {
		return;
	}

	const setCurrent = ( active ) => {
		links.forEach( ( link ) => {
			link.classList.toggle( 'is-active', link === active );
			if ( link === active ) {
				link.setAttribute( 'aria-current', 'location' );
			} else {
				link.removeAttribute( 'aria-current' );
			}
		} );
	};

	const pick = () => {
		const line = offsetOf( nav ) + 8;
		let current = map[ 0 ];
		map.forEach( ( entry ) => {
			if ( entry.heading.getBoundingClientRect().top - line <= 0 ) {
				current = entry;
			}
		} );
		setCurrent( current.link );
	};

	pick();
	window.addEventListener( 'scroll', pick, { passive: true } );
	window.addEventListener( 'resize', pick );
};

// ── Reading progress: mark sections as read when scrolled past ────────────────

const initProgressTracking = ( nav ) => {
	if ( nav.getAttribute( 'data-tocguide-progress' ) !== '1' ) {
		return;
	}
	if ( typeof window.IntersectionObserver === 'undefined' ) {
		return;
	}

	const entries = Array.from(
		nav.querySelectorAll( '.tocguide__link[href^="#"]' )
	)
		.map( ( link ) => {
			const id = decodeURIComponent(
				( link.getAttribute( 'href' ) || '' ).slice( 1 )
			);
			const heading = id ? document.getElementById( id ) : null;
			const item = link.closest( '.tocguide__item' );
			return heading && item ? { heading, item } : null;
		} )
		.filter( Boolean );

	if ( ! entries.length ) {
		return;
	}

	// eslint-disable-next-line no-undef
	const observer = new IntersectionObserver(
		( changes ) => {
			changes.forEach( ( change ) => {
				const entry = entries.find(
					( e ) => e.heading === change.target
				);
				if ( ! entry ) {
					return;
				}
				if (
					! change.isIntersecting &&
					change.boundingClientRect.top < 0
				) {
					entry.item.classList.add( 'is-read' );
				} else if ( change.isIntersecting ) {
					entry.item.classList.remove( 'is-read' );
				}
			} );
		},
		{ threshold: 0 }
	);

	entries.forEach( ( { heading } ) => observer.observe( heading ) );
};

// ── Emoji reactions (localStorage, no server required) ────────────────────────

const reactionKey = ( postId, slug, emoji ) =>
	`tocguide-r-${ postId }-${ slug }-${ emoji }`;

const initReactions = ( nav ) => {
	const postId = nav.getAttribute( 'data-tocguide-post' );
	if ( ! postId ) {
		return;
	}

	nav.querySelectorAll( '.tocguide__reaction' ).forEach( ( btn ) => {
		const item = btn.closest( '.tocguide__item' );
		const slug = item ? item.getAttribute( 'data-tocguide-slug' ) : null;
		const emoji = btn.getAttribute( 'data-reaction' );
		if ( ! slug || ! emoji ) {
			return;
		}

		// Restore persisted state.
		try {
			const saved = localStorage.getItem(
				reactionKey( postId, slug, emoji )
			);
			if ( saved === '1' ) {
				btn.setAttribute( 'aria-pressed', 'true' );
			}
		} catch {
			// localStorage may be blocked (private mode, security policy).
		}

		btn.addEventListener( 'click', () => {
			const pressed = btn.getAttribute( 'aria-pressed' ) === 'true';
			const next = ! pressed;
			btn.setAttribute( 'aria-pressed', next ? 'true' : 'false' );

			try {
				const key = reactionKey( postId, slug, emoji );
				if ( next ) {
					localStorage.setItem( key, '1' );
				} else {
					localStorage.removeItem( key );
				}
			} catch {
				// localStorage may be blocked.
			}

			if ( ! prefersReduced() ) {
				btn.classList.add( 'is-popped' );
				setTimeout( () => btn.classList.remove( 'is-popped' ), 300 );
			}
		} );
	} );
};

// ── Author section notes (toggle reveal) ─────────────────────────────────────

const initNotes = ( nav ) => {
	nav.querySelectorAll( '.tocguide__note-toggle' ).forEach( ( btn ) => {
		btn.addEventListener( 'click', () => {
			const expanded = btn.getAttribute( 'aria-expanded' ) === 'true';
			btn.setAttribute( 'aria-expanded', expanded ? 'false' : 'true' );
			const targetId = btn.getAttribute( 'aria-controls' );
			const note = targetId ? document.getElementById( targetId ) : null;
			if ( note ) {
				if ( expanded ) {
					note.setAttribute( 'hidden', '' );
				} else {
					note.removeAttribute( 'hidden' );
				}
			}
		} );
	} );
};

// ── Per-section academic citations ────────────────────────────────────────────

const MONTHS = [
	'January',
	'February',
	'March',
	'April',
	'May',
	'June',
	'July',
	'August',
	'September',
	'October',
	'November',
	'December',
];

const formatCitation = ( meta, headingText, slug, style ) => {
	const { author, title, site, date, url } = meta;
	const sectionUrl = url + '#' + slug;
	const dateObj = date ? new Date( date ) : new Date();
	const year = dateObj.getUTCFullYear();
	const monthLong = MONTHS[ dateObj.getUTCMonth() ];
	const day = dateObj.getUTCDate();

	switch ( style ) {
		case 'mla':
			return `${ author }. "${ headingText }." ${ title }, ${ site }, ${ day } ${ monthLong } ${ year }, ${ sectionUrl }.`;
		case 'chicago':
			return `${ author }. "${ headingText }." ${ title }. ${ site }. ${ monthLong } ${ day }, ${ year }. ${ sectionUrl }.`;
		case 'harvard':
			return `${ author } (${ year }) '${ headingText }' in ${ title }. ${ site }. Available at: ${ sectionUrl }.`;
		case 'plain':
			return `"${ headingText }" — ${ title } (${ sectionUrl })`;
		case 'apa':
		default:
			return `${ author }. (${ year }, ${ monthLong } ${ day }). ${ headingText }. In ${ title }. ${ site }. ${ sectionUrl }`;
	}
};

/**
 * @param {HTMLElement}      btn The button to mark as copied.
 * @param {HTMLElement|null} nav Optional nav for live-region announcement.
 */
const showCopied = ( btn, nav ) => {
	btn.classList.add( 'is-copied' );
	if ( nav ) {
		announce( nav, 'Citation copied.' );
	}
	setTimeout( () => btn.classList.remove( 'is-copied' ), 2200 );
};

/**
 * @param {string}           text Text to copy.
 * @param {HTMLElement}      btn  Button triggering the copy.
 * @param {HTMLElement|null} nav  Optional nav for live-region announcement.
 */
const copyText = ( text, btn, nav = null ) => {
	if ( navigator.clipboard && navigator.clipboard.writeText ) {
		navigator.clipboard
			.writeText( text )
			.then( () => showCopied( btn, nav ) );
		return;
	}
	// execCommand fallback for older browsers.
	const ta = document.createElement( 'textarea' );
	ta.value = text;
	ta.style.cssText = 'position:fixed;opacity:0;top:0;left:0';
	document.body.appendChild( ta );
	ta.focus();
	ta.select();
	try {
		document.execCommand( 'copy' );
		showCopied( btn, nav );
	} finally {
		document.body.removeChild( ta );
	}
};

const initCitations = ( nav ) => {
	const rawMeta = nav.getAttribute( 'data-tocguide-meta' );
	if ( ! rawMeta ) {
		return;
	}
	let meta;
	try {
		meta = JSON.parse( rawMeta );
	} catch {
		return;
	}
	const style = meta.citationStyle || 'apa';

	nav.querySelectorAll( '.tocguide__cite-btn' ).forEach( ( btn ) => {
		const item = btn.closest( '.tocguide__item' );
		const slug = item ? item.getAttribute( 'data-tocguide-slug' ) : null;
		const headingText = item
			? item.getAttribute( 'data-tocguide-heading' )
			: null;
		if ( ! slug || ! headingText ) {
			return;
		}

		btn.addEventListener( 'click', () => {
			copyText(
				formatCitation( meta, headingText, slug, style ),
				btn,
				nav
			);
		} );
	} );
};

// ── Reader annotation notes (localStorage) ────────────────────────────────────

/**
 * @param {string} postId Post ID string.
 * @param {string} slug   Section slug.
 * @return {string} localStorage key.
 */
const noteKey = ( postId, slug ) => `tocguide-rn-${ postId }-${ slug }`;

/**
 * @param {HTMLElement} nav The TOC nav element.
 */
const initReaderNotes = ( nav ) => {
	const postId = nav.getAttribute( 'data-tocguide-post' );
	if ( ! postId ) {
		return;
	}

	nav.querySelectorAll( '.tocguide__rnote-toggle' ).forEach( ( btn ) => {
		const item = btn.closest( '.tocguide__item' );
		const slug = item ? item.getAttribute( 'data-tocguide-slug' ) : null;
		if ( ! slug ) {
			return;
		}

		const padId = btn.getAttribute( 'aria-controls' );
		const pad = padId ? document.getElementById( padId ) : null;
		const ta = pad ? pad.querySelector( '.tocguide__rnote-ta' ) : null;
		if ( ! pad || ! ta ) {
			return;
		}

		// Restore saved note and show indicator.
		try {
			const saved = localStorage.getItem( noteKey( postId, slug ) );
			if ( saved ) {
				ta.value = saved;
				btn.classList.add( 'has-content' );
			}
		} catch {
			// localStorage may be blocked.
		}

		// Toggle open/close.
		btn.addEventListener( 'click', () => {
			const expanded = btn.getAttribute( 'aria-expanded' ) === 'true';
			btn.setAttribute( 'aria-expanded', expanded ? 'false' : 'true' );
			if ( expanded ) {
				pad.setAttribute( 'hidden', '' );
			} else {
				pad.removeAttribute( 'hidden' );
				ta.focus();
			}
		} );

		// Debounced auto-save.
		let saveTimer;
		ta.addEventListener( 'input', () => {
			clearTimeout( saveTimer );
			saveTimer = setTimeout( () => {
				try {
					const val = ta.value.trim();
					if ( val ) {
						localStorage.setItem( noteKey( postId, slug ), val );
						btn.classList.add( 'has-content' );
					} else {
						localStorage.removeItem( noteKey( postId, slug ) );
						btn.classList.remove( 'has-content' );
					}
				} catch {
					// localStorage may be blocked.
				}
			}, 400 );
		} );
	} );
};

// ── Reading progress bar (% of headings scrolled past) ────────────────────────

/**
 * @param {HTMLElement} nav The TOC nav element.
 */
const initReadingProgress = ( nav ) => {
	if ( nav.getAttribute( 'data-tocguide-reader-progress' ) !== '1' ) {
		return;
	}
	if ( typeof window.IntersectionObserver === 'undefined' ) {
		return;
	}

	const bar = nav.querySelector( '.tocguide__reading-bar' );
	if ( ! bar ) {
		return;
	}

	const links = Array.from(
		nav.querySelectorAll( '.tocguide__link[href^="#"]' )
	);
	if ( ! links.length ) {
		return;
	}

	const headings = links
		.map( ( link ) => {
			const id = decodeURIComponent(
				( link.getAttribute( 'href' ) || '' ).slice( 1 )
			);
			return id ? document.getElementById( id ) : null;
		} )
		.filter( Boolean );

	if ( ! headings.length ) {
		return;
	}

	const wrap = nav.querySelector( '.tocguide__reading-wrap' );
	let passed = 0;

	const update = () => {
		const pct = headings.length
			? Math.round( ( passed / headings.length ) * 100 )
			: 0;
		bar.style.width = pct + '%';
		if ( wrap ) {
			wrap.setAttribute( 'aria-valuenow', String( pct ) );
		}
	};

	// eslint-disable-next-line no-undef
	const observer = new IntersectionObserver(
		( changes ) => {
			changes.forEach( ( change ) => {
				// Count a heading as "read" once it has scrolled above the fold.
				if (
					! change.isIntersecting &&
					change.boundingClientRect.top < 0
				) {
					const idx = headings.indexOf( change.target );
					if ( idx !== -1 ) {
						passed = Math.max( passed, idx + 1 );
					}
				}
			} );
			update();
		},
		{ threshold: 0 }
	);

	headings.forEach( ( h ) => observer.observe( h ) );
};

// ── Resume reading bookmark (localStorage) ────────────────────────────────────

/**
 * @param {string} postId Post ID string.
 * @return {string} localStorage key.
 */
const bookmarkKey = ( postId ) => `tocguide-bm-${ postId }`;

/**
 * @param {HTMLElement} nav The TOC nav element.
 */
const initBookmark = ( nav ) => {
	const postId = nav.getAttribute( 'data-tocguide-post' );
	if ( ! postId ) {
		return;
	}
	if ( nav.getAttribute( 'data-tocguide-bookmark' ) !== '1' ) {
		return;
	}
	if ( typeof window.IntersectionObserver === 'undefined' ) {
		return;
	}

	const resumeBtn = nav.querySelector( '.tocguide__resume-btn' );
	const key = bookmarkKey( postId );

	// Track the last visible heading as the reader scrolls.
	// Writes are debounced to avoid hammering localStorage on every
	// IntersectionObserver callback during fast scrolling.
	const links = Array.from(
		nav.querySelectorAll( '.tocguide__link[href^="#"]' )
	);

	let saveTimer;
	// eslint-disable-next-line no-undef
	const tracker = new IntersectionObserver(
		( changes ) => {
			changes.forEach( ( change ) => {
				if ( change.isIntersecting ) {
					const id = change.target.id;
					clearTimeout( saveTimer );
					saveTimer = setTimeout( () => {
						try {
							localStorage.setItem( key, id );
						} catch {
							// localStorage may be blocked.
						}
					}, 500 );
				}
			} );
		},
		{ rootMargin: '-30% 0px -60% 0px', threshold: 0 }
	);

	links.forEach( ( link ) => {
		const id = decodeURIComponent(
			( link.getAttribute( 'href' ) || '' ).slice( 1 )
		);
		const heading = id ? document.getElementById( id ) : null;
		if ( heading ) {
			tracker.observe( heading );
		}
	} );

	// Show the Resume button if a bookmark exists.
	try {
		const saved = localStorage.getItem( key );
		if ( saved && resumeBtn ) {
			const target = document.getElementById( saved );
			// Escape the slug for a CSS attribute-value selector.
			const escapedSlug = saved.replace(
				/([!"#$%&'()*+,./:;<=>?@[\\\]^`{|}~])/g,
				'\\$1'
			);
			const targetLink = target
				? nav.querySelector( `a[href="#${ escapedSlug }"]` )
				: null;
			if ( targetLink ) {
				resumeBtn.removeAttribute( 'hidden' );
				resumeBtn.addEventListener( 'click', () => {
					targetLink.click();
					// Remove the Resume button after use so it's not confusing.
					resumeBtn.setAttribute( 'hidden', '' );
				} );
			}
		}
	} catch {
		// localStorage may be blocked.
	}
};

// ── Section hover-preview tooltip ────────────────────────────────────────────

/**
 * Show a floating tooltip with the section's opening text when the reader
 * hovers over (or focuses) a TOC link. Works independently of guide mode.
 *
 * Uses one shared `position: fixed` bubble per nav so the tooltip escapes
 * any overflow:hidden or max-height constraints on the nav container.
 *
 * @param {HTMLElement} nav The TOC nav element.
 */
const initHoverPreviews = ( nav ) => {
	const items = Array.from(
		nav.querySelectorAll( '.tocguide__item.has-hover-preview' )
	);
	if ( ! items.length ) {
		return;
	}

	const bubble = document.createElement( 'div' );
	bubble.className = 'tocguide__tip-bubble';
	bubble.setAttribute( 'aria-hidden', 'true' );
	document.body.appendChild( bubble );

	let hideTimer = null;

	const positionBubble = ( anchor ) => {
		const rect = anchor.getBoundingClientRect();
		const gap = 12;
		const vw = window.innerWidth;
		const vh = window.innerHeight;
		const bw = Math.min( 280, vw - 24 );

		bubble.style.maxWidth = bw + 'px';
		// Measure height off-screen first.
		bubble.style.visibility = 'hidden';
		bubble.style.top = '-9999px';
		bubble.style.left = '0px';

		const bh = bubble.offsetHeight;

		// Prefer placing to the right; fall back to the left.
		let left = rect.right + gap;
		let side = 'is-right';
		if ( left + bw > vw - 8 ) {
			left = rect.left - gap - bw;
			side = 'is-left';
		}
		left = Math.max( 8, left );

		// Center vertically on the anchor; clamp to viewport.
		let top = rect.top + rect.height / 2 - bh / 2;
		top = Math.max( 8, Math.min( top, vh - bh - 8 ) );

		bubble.classList.remove( 'is-right', 'is-left' );
		bubble.classList.add( side );
		bubble.style.visibility = '';
		bubble.style.top = Math.round( top ) + 'px';
		bubble.style.left = Math.round( left ) + 'px';
	};

	const showBubble = ( item ) => {
		clearTimeout( hideTimer );
		const label = item.querySelector( '.tocguide__tip-label' );
		const text = label ? label.textContent.trim() : '';
		if ( ! text ) {
			return;
		}
		const anchor = item.querySelector( '.tocguide__link' ) || item;
		bubble.textContent = text;
		bubble.classList.add( 'is-visible' );
		positionBubble( anchor );
	};

	const hideBubble = () => {
		clearTimeout( hideTimer );
		// Brief delay so moving between tight items feels smooth.
		hideTimer = setTimeout(
			() => bubble.classList.remove( 'is-visible' ),
			60
		);
	};

	items.forEach( ( item ) => {
		const link = item.querySelector( '.tocguide__link' );
		item.addEventListener( 'mouseenter', () => showBubble( item ) );
		item.addEventListener( 'mouseleave', hideBubble );
		if ( link ) {
			link.addEventListener( 'focus', () => showBubble( item ) );
			link.addEventListener( 'blur', hideBubble );
		}
	} );
};

// ── Export / print bar ────────────────────────────────────────────────────────

/**
 * Collect the TOC as a flat [{depth, text, slug}] array from the rendered DOM.
 *
 * @param {HTMLElement} nav The TOC nav element.
 * @return {Array<{depth: number, text: string, slug: string}>} Flat ordered list.
 */
const collectItems = ( nav ) => {
	const items = [];
	nav.querySelectorAll( '.tocguide__item' ).forEach( ( li ) => {
		const link = li.querySelector( '.tocguide__link' );
		if ( ! link ) {
			return;
		}
		const text = link.textContent.trim();
		const slug = ( link.getAttribute( 'href' ) || '' ).replace( /^#/, '' );
		// Depth = nesting level (tocguide__list = 1, tocguide__sub = 2, …).
		let depth = 1;
		let parent = li.parentElement;
		while ( parent && ! parent.classList.contains( 'tocguide__body' ) ) {
			if (
				parent.classList.contains( 'tocguide__sub' ) ||
				parent.tagName === 'OL' ||
				parent.tagName === 'UL'
			) {
				depth++;
			}
			parent = parent.parentElement;
		}
		depth = Math.max( 1, depth );
		items.push( { depth, text, slug } );
	} );
	return items;
};

/**
 * Convert collected TOC items to a Markdown string.
 *
 * @param {string}                                             title Post title.
 * @param {Array<{depth: number, text: string, slug: string}>} items TOC items.
 * @param {string}                                             url   Current page URL.
 * @return {string} Markdown-formatted outline.
 */
const toMarkdown = ( title, items, url ) => {
	const lines = [ `# ${ title }`, '' ];
	const minDepth = items.reduce(
		( m, i ) => Math.min( m, i.depth ),
		Infinity
	);
	items.forEach( ( item ) => {
		const indent = '  '.repeat( item.depth - minDepth );
		lines.push( `${ indent }- [${ item.text }](${ url }#${ item.slug })` );
	} );
	lines.push( '' );
	return lines.join( '\n' );
};

/**
 * Build a minimal Word-compatible HTML string for the outline.
 *
 * @param {string}                                             title Post title.
 * @param {Array<{depth: number, text: string, slug: string}>} items TOC items.
 * @param {string}                                             url   Current page URL.
 * @return {string} HTML document string.
 */
const toWordHtml = ( title, items, url ) => {
	const esc = ( s ) =>
		s
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' );
	let inner = `<h1>${ esc( title ) }</h1><ul>`;
	const minDepth = items.reduce(
		( m, i ) => Math.min( m, i.depth ),
		Infinity
	);
	let prevDepth = minDepth;
	items.forEach( ( item ) => {
		if ( item.depth > prevDepth ) {
			inner += '<ul>'.repeat( item.depth - prevDepth );
		} else if ( item.depth < prevDepth ) {
			inner += '</ul></li>'.repeat( prevDepth - item.depth );
		}
		inner += `<li><a href="${ esc( url ) }#${ esc( item.slug ) }">${ esc(
			item.text
		) }</a>`;
		prevDepth = item.depth;
	} );
	inner += '</li></ul>'.repeat( prevDepth - minDepth + 1 );
	return (
		`<!DOCTYPE html>\n<html><head><meta charset="utf-8">` +
		`<title>${ esc( title ) }</title></head><body>${ inner }</body></html>`
	);
};

/**
 * Trigger a browser file download.
 *
 * @param {string} content  File content.
 * @param {string} filename Suggested file name.
 * @param {string} mime     MIME type.
 */
const downloadFile = ( content, filename, mime ) => {
	const blob = new Blob( [ content ], { type: mime } );
	const href = URL.createObjectURL( blob );
	const a = document.createElement( 'a' );
	a.href = href;
	a.download = filename;
	document.body.appendChild( a );
	a.click();
	document.body.removeChild( a );
	setTimeout( () => URL.revokeObjectURL( href ), 10000 );
};

/**
 * Open a minimal print window containing only the TOC outline.
 *
 * @param {string} title Post title.
 * @param {string} html  Word-compatible HTML outline (reused for print).
 */
const printOutline = ( title, html ) => {
	const win = window.open( '', '_blank', 'width=800,height=600' );
	if ( ! win ) {
		return;
	}
	win.document.write(
		html.replace(
			'</head>',
			`<style>body{font-family:sans-serif;max-width:640px;margin:2rem auto}` +
				`a{color:inherit}h1{font-size:1.4rem;margin-bottom:1rem}` +
				`ul,ol{padding-left:1.5rem}li{margin:.3rem 0}</style></head>`
		)
	);
	win.document.close();
	win.focus();
	win.print();
};

/**
 * Wire up export / print buttons in the toolbar.
 *
 * @param {HTMLElement} nav The TOC nav element.
 */
const initExport = ( nav ) => {
	const bar = nav.querySelector( '.tocguide__export-bar' );
	if ( ! bar ) {
		return;
	}

	const pageTitle =
		bar.getAttribute( 'data-tocguide-export-title' ) ||
		document.title ||
		'Table of Contents';
	const pageUrl = window.location.href.split( '#' )[ 0 ];
	const slug = pageTitle
		.toLowerCase()
		.replace( /[^a-z0-9]+/g, '-' )
		.replace( /(^-|-$)/g, '' );

	bar.querySelectorAll( '.tocguide__export-btn' ).forEach( ( btn ) => {
		const action = btn.getAttribute( 'data-tocguide-action' );

		btn.addEventListener( 'click', () => {
			const items = collectItems( nav );
			const md = toMarkdown( pageTitle, items, pageUrl );
			const docHtml = toWordHtml( pageTitle, items, pageUrl );

			switch ( action ) {
				case 'copy-md': {
					const onCopy = () => {
						announce( nav, 'Outline copied as Markdown.' );
						btn.classList.add( 'is-copied' );
						const confirm = btn.querySelector(
							'.tocguide__export-confirm'
						);
						if ( confirm ) {
							confirm.textContent = '✓';
						}
						setTimeout( () => {
							btn.classList.remove( 'is-copied' );
							if ( confirm ) {
								confirm.textContent = '';
							}
						}, 2200 );
					};

					if (
						navigator.clipboard &&
						navigator.clipboard.writeText
					) {
						navigator.clipboard.writeText( md ).then( onCopy );
					} else {
						const ta = document.createElement( 'textarea' );
						ta.value = md;
						ta.style.cssText =
							'position:fixed;opacity:0;top:0;left:0';
						document.body.appendChild( ta );
						ta.focus();
						ta.select();
						try {
							document.execCommand( 'copy' );
							onCopy();
						} finally {
							document.body.removeChild( ta );
						}
					}
					break;
				}
				case 'download-md':
					downloadFile( md, `${ slug }.md`, 'text/markdown' );
					announce( nav, 'Markdown file downloaded.' );
					break;
				case 'download-doc':
					downloadFile(
						docHtml,
						`${ slug }.doc`,
						'application/msword'
					);
					announce( nav, 'Word document downloaded.' );
					break;
				case 'print':
					printOutline( pageTitle, docHtml );
					break;
				default:
					break;
			}
		} );
	} );
};

// ── Bootstrap ─────────────────────────────────────────────────────────────────

let navInstance = 0;

const initNav = ( nav ) => {
	if ( nav.dataset.tocguideReady ) {
		return;
	}
	nav.dataset.tocguideReady = '1';
	if ( ! nav.dataset.tocguideInstance ) {
		nav.dataset.tocguideInstance = String( navInstance );
		navInstance += 1;
	}
	initToggle( nav );
	initClose( nav );
	initFocus( nav );
	initSmoothScroll( nav );
	initScrollSpy( nav );

	if ( nav.classList.contains( 'has-hover-preview' ) ) {
		initHoverPreviews( nav );
	}

	if ( nav.classList.contains( 'has-guide-mode' ) ) {
		initProgressTracking( nav );
		initReactions( nav );
		initNotes( nav );
		initCitations( nav );
	}

	// Study tools (work independently of guide mode).
	if ( nav.classList.contains( 'has-reader-notes' ) ) {
		initReaderNotes( nav );
	}
	if ( nav.classList.contains( 'has-reading-progress' ) ) {
		initReadingProgress( nav );
	}
	if ( nav.classList.contains( 'has-bookmark' ) ) {
		initBookmark( nav );
	}

	if ( nav.classList.contains( 'has-export' ) ) {
		initExport( nav );
	}

	applyDock();
};

domReady( () => {
	document
		.querySelectorAll( '.wp-block-tocguide-table-of-contents, .tocguide' )
		.forEach( initNav );
} );
