<?php
class Check_Links implements themecheck {
	protected $error = array();

	protected $theme = array();

	function set_context( $data ) {
		if ( isset( $data['theme'] ) ) {
			$this->theme = $data['theme'];
		}
	}


	function check( $php_files, $css_files, $other_files ) {
		$ret = true;

		foreach ( $php_files as $php_key => $phpfile ) {
			checkcount();
			$grep = '';
			// regex borrowed from TAC
			$url_re   = '([[:alnum:]\-\.])+(\\.)([[:alnum:]]){2,4}([[:blank:][:alnum:]\/\+\=\%\&\_\\\.\~\?\-]*)';
			$title_re = '[[:blank:][:alnum:][:punct:]]*';   // 0 or more: any num, letter(upper/lower) or any punc symbol
			$space_re = '(\\s*)';
			if ( preg_match_all( '/(<a)(\\s+)(href' . $space_re . '=' . $space_re . '"' . $space_re . '((http|https|ftp):\\/\\/)?)' . $url_re . '("' . $space_re . $title_re . $space_re . '>)' . $title_re . '(<\\/a>)/is', $phpfile, $out, PREG_SET_ORDER ) ) {
				$filename = tc_filename( $php_key );
				foreach ( $out as $key ) {
					if ( preg_match( '/\<a\s?href\s?=\s?["|\'](.*?)[\'|"](.*?)\>(.*?)\<\/a\>/is', $key[0], $stripped ) ) {
						if (
							! empty( $this->theme['AuthorURI'] ) &&
							! empty( $this->theme['URI'] ) &&
							$stripped[1] &&
							! strpos( $stripped[1], $this->theme['URI'] ) &&
							! strpos( $stripped[1], $this->theme['AuthorURI'] ) &&
							! stripos( $stripped[1], 'WordPress.' )
						) {
							$grep .= tc_grep( $stripped[1], $php_key );
						}
					}
				}
				if ( $grep ) {
					$this->error[] = sprintf(
						'<span class="tc-lead tc-info">%s</span>: %s %s',
						__( 'INFO', 'theme-check' ),
						sprintf(
							__( 'Possible hard-coded links were found in the file %s.', 'theme-check' ),
							'<strong>' . $filename . '</strong>'
						),
						$grep
					);
				}
			}
		}
		return $ret;
	}
	function getError() {
		return $this->error;
	}
}
$themechecks[] = new Check_Links();
