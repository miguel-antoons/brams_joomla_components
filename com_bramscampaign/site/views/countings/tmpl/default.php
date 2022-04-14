<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramscampaign
 *
 * ! NOTE that this file and its content will probably be deleted in the near future
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>

<div class="container custom_container container_margin">
	<?php echo '<input id="token" type="hidden" name="' . JSession::getFormToken() . '" value="1" />'; ?>
	<div class='row'>
		<div class='col custom_col'>
			<p id='message'>

			</p>
			<h1>Countings</h1>
			<p>
				Click one the button in the right column to add meteors to the counting.
			</p>
		</div>
	</div>

	<div class='row'>
		<div class='col'>
			<table class='table'>
				<thead>
				<tr>
					<th class='headerCol' onclick="sortTable(this, 'name')">
						Name <i id='sortIcon' class="fa fa-sort" aria-hidden="true"></i>
					</th>
					<th class='headerCol' onclick="sortTable(this, 'station')">
						Station
					</th>
					<th class='headerCol' onclick="sortTable(this, 'start')">
						Start
					</th>
					<th class='headerCol' onclick="sortTable(this, 'end')">
						End
					</th>
					<th class='headerCol'>
						Actions
					</th>
				</tr>
				</thead>
				<tbody id='campaigns'>

				</tbody>
			</table>
		</div>
	</div>
</div>
