<?php
/**
 * SoftwareVersion extension - customizes Special:Version for wikis
 * by changing MediaWiki's version to $wgVersion and adding local wiki component
 *
 * This was originally developed for ShoutWiki but has been rewritten so it's easier
 * for third-parties to use this extension.
 *
 * @file
 * @ingroup Extensions
 * @author Jack Phoenix <jack@shoutwiki.com>
 * @copyright Copyright © 2009-2017 Jack Phoenix
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class SoftwareVersion {

	/**
	 * Adds local wiki component into Special:Version and sets MW's version to $wgVersion
	 *
	 * @param array $software Array of software information
	 * @return bool
	 */
	public static function addSVNInfo( &$software ) {
		global $wgVersion, $wgCanonicalServer, $wgSitename,
			$wgSoftwareVersionExecutablePath, $IP;

		// Set MW version to $wgVersion
		$software['[https://www.mediawiki.org/ MediaWiki]'] = $wgVersion;

		// This is ugly, or at least uglier than in the past, because it wouldn't
		// work anymore on my local machine.
		// First, we do a "svn info $IP", then JSON-encode the result, and explode
		// along newlines and use PHP's array access to access the revision number
		// and last changed date, which will be added to the version table.
		//
		// Why is this so ugly? Because svn info returns a string, instead of a
		// sane array that we could easily manipulate.
		//
		// We also strip out the English words from the svn info output, so the
		// final output that an end-user viewing Special:Version sees is something
		// like "r1811 (2012-05-16 00:31:45 +0300)".
		if ( !wfIsWindows() ) {
			$svnInfo = wfShellExec( $wgSoftwareVersionExecutablePath['unix'] . ' info ' . $IP, $error );
			$newline = "\n";
		} else {
			$svnInfo = wfShellExec( $wgSoftwareVersionExecutablePath['windows'] . ' info ' . $IP, $error );
			$newline = "\r\n";
		}

		$json = json_encode( $svnInfo );
		$exploded = explode( $newline, $svnInfo );

		if ( wfMessage( 'softwareversion-wiki-link' )->isDisabled() ) {
			$wikiLink = "[$wgCanonicalServer $wgSitename]";
		} else {
			$wikiLink = wfMessage( 'softwareversion-wiki-link' )->text();
		}

		// Add local wiki component (revision number and last changed date)
		$software[$wikiLink] =
			str_replace( 'Revision: ', 'r', $exploded[6] ) /* Revision */ .
			' (' .
			str_replace( 'Last Changed Date: ', '', preg_replace( '/ \(.*\)/', '', $exploded[11] ) ) ./* Last Changed Date */
			')';

		return true;
	}

}
