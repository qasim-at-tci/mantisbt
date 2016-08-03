<?php

if (class_exists('Squiz_Sniffs_Arrays_ArrayDeclarationSniff', true) === false) {
	throw new PHP_CodeSniffer_Exception('Class Squiz_Sniffs_Arrays_ArrayDeclarationSniff not found');
}

/**
 * A test to ensure that arrays conform to the array coding standard.
 * Extends the original sniff to check for presence of a  single space after
 * the opening and before the closing parenthesis.
 */
class Mantis_Sniffs_Arrays_ArrayDeclarationSniff extends Squiz_Sniffs_Arrays_ArrayDeclarationSniff
{
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
	{
		parent::process( $phpcsFile, $stackPtr );

		$tokens = $phpcsFile->getTokens();
		$arrayStart = $tokens[$stackPtr]['parenthesis_opener'];
		if( isset($tokens[$arrayStart]['parenthesis_closer'] ) === false ) {
			return;
		}
		$arrayEnd = $tokens[$arrayStart]['parenthesis_closer'];

		# Single-line arrays only
		if( $tokens[$arrayStart]['line'] === $tokens[$arrayEnd]['line'] ) {

			# Don't process empty arrays
			if( $tokens[$arrayStart + 1]['code'] !== ')' ) {
				return;
			}

			# Check space after opening parenthesis
			if( $tokens[$arrayStart + 1]['code'] !== T_WHITESPACE ) {
				$content = $tokens[($arrayStart + 1)]['content'];
				$error   = 'Expected 1 space after array opening bracket; 0 found';
				$data    = array( $content );
				$fix     = $phpcsFile->addFixableError( $error, $arrayStart, 'NoSpaceAfterOpenBracket', $data );
				if ($fix === true) {
					$phpcsFile->fixer->addContentBefore($arrayStart + 1, ' ');
				}
			} else {
				$spaceLength = $tokens[($arrayStart + 1)]['length'];
				if ($spaceLength !== 1) {
					$content = $tokens[($arrayStart + 1)]['content'];
					$error   = 'Expected 1 space after array opening bracket; %s found';
					$data    = array( $spaceLength );
					$fix     = $phpcsFile->addFixableError( $error, $arrayStart, 'SpaceAfterOpenBracket', $data );
					if ($fix === true) {
						$phpcsFile->fixer->replaceToken($arrayStart + 1, ' ');
					}
				}
			}

			# Check space before closing parenthesis
			if( $tokens[$arrayEnd - 1]['code'] !== T_WHITESPACE ) {
				$content = $tokens[($arrayEnd - 1)]['content'];
				$error   = 'Expected 1 space before array closing bracket; 0 found';
				$data    = array( $content );
				$fix     = $phpcsFile->addFixableError( $error, $arrayEnd, 'NoSpaceBeforeCloseBracket', $data );
				if ($fix === true) {
					$phpcsFile->fixer->addContentBefore($arrayEnd, ' ');
				}
			} else {
				$spaceLength = $tokens[($arrayEnd - 1)]['length'];
				if ($spaceLength !== 1) {
					$content = $tokens[($arrayEnd - 1)]['content'];
					$error   = 'Expected 1 space before array closing bracket; %s found';
					$data    = array( $spaceLength );
					$fix     = $phpcsFile->addFixableError( $error, $arrayEnd , 'SpaceBeforeCloseBracket', $data );
					if ($fix === true) {
						$phpcsFile->fixer->replaceToken($arrayEnd - 1, ' ');
					}
				}
			}

		}
	}
}
