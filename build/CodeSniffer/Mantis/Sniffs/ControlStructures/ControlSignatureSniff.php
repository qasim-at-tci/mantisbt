<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Sniff: Mantis.Arrays.ArrayDeclaration
 *
 * CodeSniffer rule for MantisBT coding guidelines.
 * Extends Squiz standard's ArrayDeclarationSniff
 *
 * @package    MantisBT_build
 * @subpackage CodeSniffer
 * @copyright  Copyright 2021  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link       https://www.mantisbt.org
 */

namespace Mantis\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Standards\PSR2\Sniffs\ControlStructures\ControlStructureSpacingSniff as PSR2ArrayDeclarationSniff;


/**
 * A test to ensure that arrays conform to the array coding standard.
 *
 * Extends the original sniff to check for presence of a single space after
 * the opening, and before the closing parenthesis.
 */
class ControlStructureSpacingSniff extends PSR2ArrayDeclarationSniff
{
	public $requiredSpacesAfterOpen = 1;
	public $requiredSpacesBeforeClose = 1;

	/**
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token
	 *                        in the stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		parent::process( $phpcsFile, $stackPtr );
	}
}
