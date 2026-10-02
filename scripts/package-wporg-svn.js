#!/usr/bin/env node
/**
 * Assemble a WordPress.org SVN tree for TOCguide.
 *
 * SVN is a release repository, not the git history. This script does not
 * commit. It writes:
 *
 *   dist/svn/trunk/    plugin files at the trunk root (no nested tocguide/)
 *   dist/svn/assets/   banner, icon, and screenshot files for the directory page
 *
 * Pass --into <checkout> to copy that tree into an `svn co` working copy and
 * schedule adds. Tagging uses `svn cp trunk tags/<version>` inside that
 * checkout. You still run `svn ci` yourself.
 *
 * @see https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/
 * @see https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/
 */

'use strict';

const { execFileSync } = require( 'child_process' );
const fs = require( 'fs' );
const os = require( 'os' );
const path = require( 'path' );

const root = path.resolve( __dirname, '..' );
const svnUrl = 'https://plugins.svn.wordpress.org/tocguide';

const ASSET_SIZES = {
	'banner-772x250.png': [ 772, 250 ],
	'banner-1544x500.png': [ 1544, 500 ],
	'icon-128x128.png': [ 128, 128 ],
	'icon-256x256.png': [ 256, 256 ],
};

const SKIP_DIR_NAMES = new Set( [
	'node_modules',
	'vendor',
	'.git',
	'tests',
	'.github',
	'.cursor',
] );

function parseArgs( argv ) {
	const args = {
		skipBuild: false,
		skipAssets: false,
		into: '',
	};
	for ( let i = 0; i < argv.length; i++ ) {
		const arg = argv[ i ];
		if ( arg === '--skip-build' ) {
			args.skipBuild = true;
		} else if ( arg === '--skip-assets' ) {
			args.skipAssets = true;
		} else if ( arg === '--into' ) {
			args.into = argv[ i + 1 ] || '';
			i++;
		} else if ( arg === '--help' || arg === '-h' ) {
			printHelp();
			process.exit( 0 );
		} else {
			throw new Error( 'Unknown argument: ' + arg );
		}
	}
	if ( argv.includes( '--into' ) && ! args.into ) {
		throw new Error( '--into requires a path to an svn checkout.' );
	}
	return args;
}

function printHelp() {
	console.log( `Usage: node scripts/package-wporg-svn.js [options]

  --into <dir>   Copy trunk/ and assets/ into an existing svn checkout.
                 Schedules svn add, image mime-types, and svn cp of the tag.
                 Does not run svn ci.
  --skip-build   Use the existing build/ directory.
  --skip-assets  Package trunk only (no banner/icon render).
` );
}

function readText( file ) {
	return fs.readFileSync( file, 'utf8' );
}

function readVersions() {
	const plugin = readText( path.join( root, 'tocguide.php' ) );
	const header = plugin.match( /^\s*\*\s*Version:\s*(\S+)\s*$/m );
	const constant = plugin.match( /define\(\s*'TOCGUIDE_VERSION',\s*'([^']+)'\s*\)/ );
	const readme = readText( path.join( root, 'readme.txt' ) );
	const stable = readme.match( /^Stable tag:\s*(\S+)\s*$/m );
	const pkg = JSON.parse( readText( path.join( root, 'package.json' ) ) );
	const block = JSON.parse( readText( path.join( root, 'src/block.json' ) ) );

	const versions = {
		header: header && header[ 1 ],
		constant: constant && constant[ 1 ],
		stable: stable && stable[ 1 ],
		package: pkg.version,
		block: block.version,
		files: Array.isArray( pkg.files ) ? pkg.files : [],
	};

	const unique = new Set(
		[ versions.header, versions.constant, versions.stable, versions.package, versions.block ].filter( Boolean )
	);
	if ( unique.size !== 1 ) {
		throw new Error(
			'Version mismatch. header=' +
				versions.header +
				' constant=' +
				versions.constant +
				' stable=' +
				versions.stable +
				' package.json=' +
				versions.package +
				' block.json=' +
				versions.block
		);
	}
	if ( ! /^\d+\.\d+(\.\d+)?$/.test( versions.stable ) ) {
		throw new Error( 'Stable tag must be a numeric version (tags/1.5.0), got ' + versions.stable );
	}
	if ( versions.stable === 'trunk' ) {
		throw new Error( 'Stable tag must be a version tag, not trunk.' );
	}
	return versions;
}

function assertShortDescription() {
	const readme = readText( path.join( root, 'readme.txt' ) );
	const body = readme.split( /^== Description ==\s*$/m )[ 0 ] || '';
	const lines = body.split( /\r?\n/ );
	const headerEnd = lines.findIndex( ( line ) => line.trim() === '' );
	const short = lines
		.slice( headerEnd + 1 )
		.join( ' ' )
		.replace( /\s+/g, ' ' )
		.trim();
	if ( ! short ) {
		throw new Error( 'readme.txt is missing the short description above == Description ==.' );
	}
	if ( short.length > 150 ) {
		throw new Error(
			'readme.txt short description is ' + short.length + ' characters. WordPress.org keeps 150.'
		);
	}
}

function screenshotCaptions() {
	const readme = readText( path.join( root, 'readme.txt' ) );
	const section = readme.split( /^== Screenshots ==\s*$/m )[ 1 ];
	if ( ! section ) {
		return [];
	}
	const untilNext = section.split( /^== /m )[ 0 ];
	const captions = [];
	untilNext.split( /\r?\n/ ).forEach( ( line ) => {
		const match = line.match( /^(\d+)\.\s+\S/ );
		if ( match ) {
			captions.push( Number( match[ 1 ] ) );
		}
	} );
	return captions;
}

function runBuild() {
	console.log( 'Building block assets…' );
	execFileSync( 'npm', [ 'run', 'build' ], { cwd: root, stdio: 'inherit' } );
}

function copyAllowlist( versions, destTrunk ) {
	if ( versions.files.length === 0 ) {
		throw new Error( 'package.json "files" is empty; trunk would not match plugin-zip.' );
	}
	fs.mkdirSync( destTrunk, { recursive: true } );
	versions.files.forEach( ( entry ) => {
		const from = path.join( root, entry );
		if ( ! fs.existsSync( from ) ) {
			throw new Error( 'Missing path listed in package.json files: ' + entry );
		}
		fs.cpSync( from, path.join( destTrunk, entry ), {
			recursive: true,
			filter: ( src ) => {
				const base = path.basename( src );
				if ( SKIP_DIR_NAMES.has( base ) ) {
					return false;
				}
				if ( base === '.DS_Store' || base === '.gitignore' || base.endsWith( '.zip' ) ) {
					return false;
				}
				return true;
			},
		} );
	} );
}

function assertTrunk( destTrunk ) {
	const main = path.join( destTrunk, 'tocguide.php' );
	if ( ! fs.existsSync( main ) ) {
		throw new Error( 'trunk/tocguide.php is missing. The main file must sit at the trunk root.' );
	}
	if ( fs.existsSync( path.join( destTrunk, 'tocguide', 'tocguide.php' ) ) ) {
		throw new Error( 'Refusing nested trunk/tocguide/tocguide.php. That breaks WordPress.org downloads.' );
	}
	const required = [ 'readme.txt', 'uninstall.php', 'license.txt', path.join( 'build', 'block.json' ) ];
	required.forEach( ( rel ) => {
		if ( ! fs.existsSync( path.join( destTrunk, rel ) ) ) {
			throw new Error( 'trunk is missing ' + rel );
		}
	} );

	const banned = [ 'node_modules', '.git', '.github', 'docs', 'composer.json', 'package.json', '.wordpress-org' ];
	banned.forEach( ( rel ) => {
		if ( fs.existsSync( path.join( destTrunk, rel ) ) ) {
			throw new Error( 'trunk must not contain ' + rel );
		}
	} );

	walk( destTrunk, ( file ) => {
		if ( file.endsWith( '.zip' ) ) {
			throw new Error( 'SVN must not contain zip files: ' + path.relative( destTrunk, file ) );
		}
	} );
}

function walk( dir, onFile ) {
	fs.readdirSync( dir, { withFileTypes: true } ).forEach( ( entry ) => {
		const full = path.join( dir, entry.name );
		if ( entry.isDirectory() ) {
			walk( full, onFile );
			return;
		}
		onFile( full );
	} );
}

function loadResvg() {
	// Optional native renderer. Loaded here so `npm ci` and `--skip-assets`
	// do not need @resvg/resvg-js installed.
	try {
		return require( '@resvg/resvg-js' ).Resvg;
	} catch ( error ) {
		const toolDir = path.join( os.tmpdir(), 'tocguide-resvg' );
		const pkgDir = path.join( toolDir, 'node_modules', '@resvg', 'resvg-js' );
		if ( ! fs.existsSync( pkgDir ) ) {
			console.log( 'Installing @resvg/resvg-js to render directory banners…' );
			fs.mkdirSync( toolDir, { recursive: true } );
			execFileSync( 'npm', [ 'install', '--no-save', '--prefix', toolDir, '@resvg/resvg-js' ], {
				stdio: 'inherit',
			} );
		}
		return require( pkgDir ).Resvg;
	}
}

function renderPng( Resvg, svgPath, pngPath, width ) {
	const svg = fs.readFileSync( svgPath );
	const resvg = new Resvg( svg, {
		fitTo: { mode: 'width', value: width },
		font: { loadSystemFonts: true },
	} );
	fs.writeFileSync( pngPath, resvg.render().asPng() );
}

function pngSize( file ) {
	const buf = fs.readFileSync( file );
	const signature = buf.toString( 'ascii', 1, 4 );
	if ( signature !== 'PNG' ) {
		throw new Error( path.basename( file ) + ' is not a PNG.' );
	}
	return {
		width: buf.readUInt32BE( 16 ),
		height: buf.readUInt32BE( 20 ),
	};
}

function copyScreenshots( sourceDir, destAssets ) {
	if ( ! fs.existsSync( sourceDir ) ) {
		return;
	}
	fs.readdirSync( sourceDir ).forEach( ( name ) => {
		if ( /^screenshot-\d+\.(png|jpe?g)$/i.test( name ) ) {
			fs.copyFileSync( path.join( sourceDir, name ), path.join( destAssets, name.toLowerCase() ) );
		}
	} );
}

function packageAssets( destAssets ) {
	const sourceDir = path.join( root, '.wordpress-org' );
	fs.mkdirSync( destAssets, { recursive: true } );

	const banner = path.join( sourceDir, 'banner.svg' );
	const icon = path.join( sourceDir, 'icon.svg' );
	if ( ! fs.existsSync( banner ) || ! fs.existsSync( icon ) ) {
		throw new Error( '.wordpress-org is missing banner.svg or icon.svg.' );
	}

	const Resvg = loadResvg();
	renderPng( Resvg, banner, path.join( destAssets, 'banner-1544x500.png' ), 1544 );
	renderPng( Resvg, banner, path.join( destAssets, 'banner-772x250.png' ), 772 );
	renderPng( Resvg, icon, path.join( destAssets, 'icon-256x256.png' ), 256 );
	renderPng( Resvg, icon, path.join( destAssets, 'icon-128x128.png' ), 128 );
	fs.copyFileSync( icon, path.join( destAssets, 'icon.svg' ) );

	copyScreenshots( sourceDir, destAssets );
	copyScreenshots( path.join( sourceDir, 'screenshots' ), destAssets );

	Object.entries( ASSET_SIZES ).forEach( ( [ name, expected ] ) => {
		const file = path.join( destAssets, name );
		const size = pngSize( file );
		if ( size.width !== expected[ 0 ] || size.height !== expected[ 1 ] ) {
			throw new Error(
				name + ' is ' + size.width + 'x' + size.height + ', expected ' + expected[ 0 ] + 'x' + expected[ 1 ]
			);
		}
	} );

	const iconSvg = readText( path.join( destAssets, 'icon.svg' ) ).trim();
	if ( ! iconSvg.startsWith( '<svg' ) ) {
		throw new Error( 'assets/icon.svg is not an SVG document.' );
	}

	const warnings = [];
	screenshotCaptions().forEach( ( number ) => {
		const png = path.join( destAssets, 'screenshot-' + number + '.png' );
		const jpg = path.join( destAssets, 'screenshot-' + number + '.jpg' );
		const jpeg = path.join( destAssets, 'screenshot-' + number + '.jpeg' );
		if ( ! fs.existsSync( png ) && ! fs.existsSync( jpg ) && ! fs.existsSync( jpeg ) ) {
			warnings.push(
				'screenshot-' + number + '.png is missing. Put it in .wordpress-org/ before the directory page can show caption ' + number + '.'
			);
		}
	} );
	return warnings;
}

function svnAvailable() {
	try {
		execFileSync( 'svn', [ '--version', '--quiet' ], { stdio: 'ignore' } );
		return true;
	} catch ( error ) {
		return false;
	}
}

function syncIntoCheckout( stage, checkout, version ) {
	const resolved = path.resolve( checkout );
	if ( ! fs.existsSync( path.join( resolved, '.svn' ) ) ) {
		throw new Error( resolved + ' is not an svn working copy (no .svn directory).' );
	}
	[ 'trunk', 'assets', 'tags' ].forEach( ( dir ) => {
		if ( ! fs.existsSync( path.join( resolved, dir ) ) ) {
			throw new Error( resolved + ' is missing /' + dir + '. Check out ' + svnUrl + ' first.' );
		}
	} );

	syncTree( path.join( stage, 'trunk' ), path.join( resolved, 'trunk' ) );
	if ( fs.existsSync( path.join( stage, 'assets' ) ) ) {
		syncTree( path.join( stage, 'assets' ), path.join( resolved, 'assets' ) );
	}

	if ( ! svnAvailable() ) {
		console.log( '\nsvn is not installed. Files are copied. Install subversion, then run the commands below.' );
		printCommitCommands( resolved, version, false );
		return;
	}

	execFileSync( 'svn', [ 'add', '--force', 'trunk', 'assets' ], { cwd: resolved, stdio: 'inherit' } );
	scheduleMissingDeletes( resolved );
	setImageMimeTypes( path.join( resolved, 'assets' ) );

	copyTagFromTrunk( resolved, version );
	printCommitCommands( resolved, version, true );
}

function copyTagFromTrunk( checkout, version ) {
	const tagRel = path.join( 'tags', version );
	const tagDir = path.join( checkout, tagRel );
	if ( ! fs.existsSync( tagDir ) ) {
		execFileSync( 'svn', [ 'cp', 'trunk', tagRel ], { cwd: checkout, stdio: 'inherit' } );
		return;
	}

	const status = execFileSync( 'svn', [ 'status', '--depth', 'empty', tagRel ], {
		cwd: checkout,
		encoding: 'utf8',
	} ).trim();
	if ( status.startsWith( 'A' ) ) {
		execFileSync( 'svn', [ 'revert', '--depth', 'infinity', tagRel ], { cwd: checkout, stdio: 'inherit' } );
		execFileSync( 'svn', [ 'cp', 'trunk', tagRel ], { cwd: checkout, stdio: 'inherit' } );
		return;
	}

	console.log(
		'\ntags/' +
			version +
			' is already published. Leave it. Bump the plugin version and Stable tag to ship another release.'
	);
}

function listRelative( dir, relBase, into ) {
	fs.readdirSync( dir, { withFileTypes: true } ).forEach( ( entry ) => {
		const rel = relBase ? path.join( relBase, entry.name ) : entry.name;
		into.add( rel );
		if ( entry.isDirectory() ) {
			listRelative( path.join( dir, entry.name ), rel, into );
		}
	} );
}

function syncTree( from, to ) {
	const expected = new Set();
	listRelative( from, '', expected );
	fs.mkdirSync( to, { recursive: true } );
	fs.cpSync( from, to, { recursive: true } );

	function prune( dir, relBase ) {
		fs.readdirSync( dir, { withFileTypes: true } ).forEach( ( entry ) => {
			if ( entry.name === '.svn' ) {
				return;
			}
			const rel = relBase ? path.join( relBase, entry.name ) : entry.name;
			const full = path.join( dir, entry.name );
			if ( ! expected.has( rel ) ) {
				fs.rmSync( full, { recursive: true, force: true } );
				return;
			}
			if ( entry.isDirectory() ) {
				prune( full, rel );
			}
		} );
	}
	prune( to, '' );
}

function scheduleMissingDeletes( checkout ) {
	const status = execFileSync( 'svn', [ 'status', 'trunk', 'assets' ], {
		cwd: checkout,
		encoding: 'utf8',
	} );
	status.split( /\r?\n/ ).forEach( ( line ) => {
		if ( ! line.startsWith( '!' ) ) {
			return;
		}
		const missing = line.slice( 8 ).trim();
		if ( missing ) {
			execFileSync( 'svn', [ 'delete', '--force', missing ], { cwd: checkout, stdio: 'inherit' } );
		}
	} );
}

function setImageMimeTypes( assetsDir ) {
	if ( ! fs.existsSync( assetsDir ) ) {
		return;
	}
	const types = {
		'.png': 'image/png',
		'.jpg': 'image/jpeg',
		'.jpeg': 'image/jpeg',
		'.gif': 'image/gif',
		'.svg': 'image/svg+xml',
	};
	fs.readdirSync( assetsDir ).forEach( ( name ) => {
		const ext = path.extname( name ).toLowerCase();
		const mime = types[ ext ];
		if ( ! mime ) {
			return;
		}
		execFileSync( 'svn', [ 'propset', 'svn:mime-type', mime, path.join( 'assets', name ) ], {
			cwd: path.dirname( assetsDir ),
			stdio: 'inherit',
		} );
	} );
}

function printCommitCommands( checkout, version, scheduled ) {
	console.log( '\nReview, then commit from the checkout (SVN publishes on commit):' );
	console.log( '  cd ' + checkout );
	if ( ! scheduled ) {
		console.log( '  svn add --force trunk assets' );
		console.log( '  svn propset svn:mime-type image/png assets/*.png' );
		console.log( '  svn propset svn:mime-type image/svg+xml assets/icon.svg' );
		console.log( '  svn cp trunk tags/' + version );
	}
	console.log( '  svn status' );
	console.log( '  svn ci -m "Tagging version ' + version + '"' );
}

function printFirstCheckout( version ) {
	console.log( '\nAfter WordPress.org approves the plugin, check out the empty repository and fill it:' );
	console.log( '  svn co ' + svnUrl + ' svn-tocguide' );
	console.log( '  npm run package:svn -- --into svn-tocguide' );
	console.log( '  cd svn-tocguide && svn status && svn ci -m "Tagging version ' + version + '"' );
	console.log( '\nTrunk holds the code. assets/ is directory artwork only. tags/' + version + ' is the release users install.' );
	console.log( 'Do not commit node_modules, zips, or .wordpress-org inside trunk.' );
}

function main() {
	const args = parseArgs( process.argv.slice( 2 ) );
	const versions = readVersions();
	assertShortDescription();

	if ( ! args.skipBuild ) {
		runBuild();
	}
	if ( ! fs.existsSync( path.join( root, 'build', 'block.json' ) ) ) {
		throw new Error( 'build/block.json is missing. Run npm run build first.' );
	}

	const stage = path.join( root, 'dist', 'svn' );
	fs.rmSync( stage, { recursive: true, force: true } );
	const destTrunk = path.join( stage, 'trunk' );
	copyAllowlist( versions, destTrunk );
	assertTrunk( destTrunk );

	const warnings = [];
	if ( ! args.skipAssets ) {
		warnings.push( ...packageAssets( path.join( stage, 'assets' ) ) );
	}

	console.log( '\nPrepared ' + path.relative( root, stage ) + ' for TOCguide ' + versions.stable );
	console.log( '  trunk/tocguide.php is at the trunk root' );
	console.log( '  Stable tag ' + versions.stable );
	warnings.forEach( ( warning ) => {
		console.log( '  warning: ' + warning );
	} );

	if ( args.into ) {
		syncIntoCheckout( stage, args.into, versions.stable );
	} else {
		printFirstCheckout( versions.stable );
	}
}

try {
	main();
} catch ( error ) {
	console.error( error.message || error );
	process.exit( 1 );
}
