<?php
/**
 * Inline HTML diff generator for PHP DiffLib.
 *
 * PHP version 5
 *
 * Copyright (c) 2009 Chris Boulton <chris.boulton@interspire.com>
 * 
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *  - Neither the name of the Chris Boulton nor the names of its contributors 
 *    may be used to endorse or promote products derived from this software 
 *    without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE 
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package DiffLib
 * @author Chris Boulton <chris.boulton@interspire.com>
 * @copyright (c) 2009 Chris Boulton
 * @license New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version 1.1
 * @link http://github.com/chrisboulton/php-diff
 */

require_once dirname(__FILE__).'/Array.php';

class Diff_Renderer_Html_Inline extends Diff_Renderer_Html_Array
{
	/**
	 * Render a and return diff with changes between the two sequences
	 * displayed inline (under each other)
	 *
	 * @return string The generated inline diff.
	 */
	public function render()
	{
		$changes = parent::render();
		$html = '';
		if(empty($changes)) {
			return $html;
		}

		//custom
		//一度に表示できる行数
		$displayLine = (!empty($this->diff->options["display_line"]))? $this->diff->options["display_line"] : NULL;
		//変更がある差分の行番号を表示するか
		$showChangeLine = (!empty($this->diff->options["show_change_line"]))? $this->diff->options["show_change_line"] : FALSE;
		$isNew = (!empty($this->diff->options["is_new"]))? $this->diff->options["is_new"] : FALSE;
		//１行のみの差分表示の有無
		$singleLine = (!empty($this->diff->options["single_line"]))? $this->diff->options["single_line"] : FALSE;

		$breakFlag = FALSE;
		$count = 0;

		$html .= '<table class="Differences DifferencesInline">';
//		$html .= '<thead>';
//		$html .= '<tr>';
//		$html .= '<th>Old</th>';
//		$html .= '<th>New</th>';
//		$html .= '<th>Differences</th>';
//		$html .= '</tr>';
//		$html .= '</thead>';
		foreach($changes as $i => $blocks) {
			// If this is a separate block, we're condensing code so output ...,
			// indicating a significant portion of the code has been collapsed as
			// it is the same
			if($i > 0) {
				$html .= '<tbody class="Skipped">';
				if ($showChangeLine) {
					$html .= '<th>&nbsp;</th>';
					$html .= '<th>&nbsp;</th>';
				}
				$html .= '<td>&hellip;</td>';
				$html .= '<td></td>';
				$html .= '</tbody>';
			}

			//custom
			if ($displayLine && $count > $displayLine && $breakFlag) {
				break;
			}

			foreach($blocks as $change) {

				//custom
				if ($displayLine && $count > $displayLine && $breakFlag) {
					break;
				}

				$html .= '<tbody class="Change'.ucfirst($change['tag']).'">';
				// Equal changes should be shown on both sides of the diff
				if($change['tag'] == 'equal') {
					foreach($change['base']['lines'] as $no => $line) {
						$count++; //custom
						$fromLine = $change['base']['offset'] + $no + 1;
						$toLine = $change['changed']['offset'] + $no + 1;
						$html .= '<tr class="Left">';
						if ($showChangeLine) {
							$html .= '<td class="row_header">'.$fromLine.'</th>';
							$html .= '<td class="row_header">'.$toLine.'</th>';
						}
						$html .= '<td class="row_diff_marker"> </td>';
						$html .= '<td class="row_content">'.$line.'</td>';
						$html .= '</tr>';

						//custom
						if ($displayLine && $count > $displayLine && $breakFlag) {
							break;
						}
					}
				}
				// Added lines only on the right side
				else if($change['tag'] == 'insert') {
					$breakFlag = TRUE;
					foreach($change['changed']['lines'] as $no => $line) {
						$count++; //custom
						$toLine = $change['changed']['offset'] + $no + 1;
						$html .= '<tr class="Right">';
						if ($showChangeLine) {
							$html .= '<td class="row_header">&nbsp;</th>';
							$html .= '<td class="row_header">'.$toLine.'</th>';
						}
						$html .= '<td class="row_diff_marker">+</td>';
						$html .= '<td class="row_content"><ins>'.$line.'</ins>&nbsp;</td>';
						$html .= '</tr>';

						//custom
						if ($displayLine && $count > $displayLine && $breakFlag) {
							break;
						}
					}
				}
				// Show deleted lines only on the left side
				else if($change['tag'] == 'delete') {
					$breakFlag = TRUE;
					foreach($change['base']['lines'] as $no => $line) {
						$count++;
						$fromLine = $change['base']['offset'] + $no + 1;
						$html .= '<tr class="Left">';
						if ($showChangeLine) {
							$html .= '<td class="row_header">'.$fromLine.'</th>';
							$html .= '<td class="row_header">&nbsp;</th>';
						}
						$html .= '<td class="row_diff_marker">−</td>';
						$html .= '<td class="row_content"><del>'.$line.'</del>&nbsp;</td>';
						$html .= '</tr>';

						//custom
						if ($displayLine && $count > $displayLine && $breakFlag) {
							break;
						}
					}
				}
				// Show modified lines on both sides
				else if($change['tag'] == 'replace') {
					$breakFlag = TRUE;
					foreach($change['base']['lines'] as $no => $line) {
						$count++;
						if (!$isNew) {
							$fromLine = $change['base']['offset'] + $no + 1;
							$html .= '<tr class="Left">';
							if ($showChangeLine) {
								$html .= '<td class="row_header">'.$fromLine.'</th>';
								$html .= '<td class="row_header">&nbsp;</th>';
							}
							if (!$singleLine || ($singleLine && str_replace([" ", "　", "<del>", "</del>", "<ins>", "</ins>"], "", $line))) {
								$html .= '<td class="row_diff_marker">−</td>';
								$html .= '<td class="row_content"><span>'.$line.'</span></td>';
							} else {
								$html .= '<td class="row_diff_marker"></td>';
							}
							$html .= '</tr>';
						}

						//custom
						if ($displayLine && $count > $displayLine && $breakFlag) {
							break;
						}
					}

					foreach($change['changed']['lines'] as $no => $line) {
						$count++;
						$toLine = $change['changed']['offset'] + $no + 1;
						$html .= '<tr class="Right">';
						if ($showChangeLine) {
							$html .= '<td class="row_header">&nbsp;</th>';
							$html .= '<td class="row_header">'.$toLine.'</th>';
						}
						if (!$singleLine || ($singleLine && str_replace([" ", "　", "<del>", "</del>", "<ins>", "</ins>"], "", $line))) {
							$html .= '<td class="row_diff_marker">+</td>';
							$html .= '<td class="row_content"><span>'.$line.'</span></td>';
						} else {
							$html .= '<td class="row_diff_marker"></td>';
						}
						$html .= '</tr>';

						//custom
						if ($displayLine && $count > $displayLine && $breakFlag) {
							break;
						}
					}
				}
				$html .= '</tbody>';

			}// for block
		}
		$html .= '</table>';
		return $html;
	}
}