<?php
	
	/**
	 * Project: Higher For Hire
	 * File: debug.core.php
	 *
	**/
	
	class Debug {
	
		/** Configuration **/
		private $decimal_places = 2;
		private $time_bar_width = 400;
		/*******************/
		
		private $list;
		private $runtime;
		
		function add($headline, $message = '') {
			$m['h'] = $headline;
			$m['m'] = $message;
			$m['t'] = time();
			$this->list[] =$m;
		}
		
		function show() {
		
			global $config;
			
			if ($config->get('core', 'debug') == '1' || @$_GET['debug'] == '1') {
				
				echo '<div style="background-color:#FFF; color:#444;">';
				echo '<p style="margin-top:10px;">&nbsp;</p>';
				echo '<div class="headline">Debug</div>';
				echo '<table width="100%">';
				
				foreach ($this->list as $l) {
					echo "<tr>
							<td width=\"10%\">" . date("H:i.s", $l['t']) . "</td>
							<td>" . $l['h'] . "</td>
							<td>" . $l['m'] . "</td>
						</tr>";
				}
				
				echo '</table>';
				echo '</div>';
			}
		}
		
		function setRuntime($time) {
			$this->runtime = $time;
		}
		
		function percentageRuntime($time) {
			return number_format(($time * 100) / $this->runtime, $this->decimal_places);
		}
		
		function makeBar($time = '') {
			$width = $this->time_bar_width;
			$bg = '#DDDDDD';
			
			if ($time == '') {
				$time = $this->runtime;
				$bg = '#676767';
			}	
			
			return '<div style="float:left; margin-left:15px; width:160px;">'.number_format($time, 5).' sec ('.$this->percentageRuntime($time).'%)</div>
				<div style="margin:5px; width:'.((($time)/$this->runtime)*$width).'px; border:1px solid #FF0000; height:3px; background-color:'.$bg.'; float:left;"></div>
				<div style="clear:left;"></div>';
		}
	}
	
?>