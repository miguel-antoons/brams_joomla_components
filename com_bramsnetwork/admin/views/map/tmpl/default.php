<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsnetwork
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>

<!-- this part has to be revises since names have changed -->
<form action="index.php?option=com_bramsnetwork&view=map" method="post" id="adminMapForm" name="adminMapForm">
	<table class="table table-striped table-hover">
		<thead>
		<tr>
			<th width="1%"><?php echo JText::_('COM_map_NUM'); ?></th>
			<th width="2%">
				<?php echo JHtml::_('grid.checkall'); ?>
			</th>
			<th width="90%">
				<?php echo JText::_('COM_map_NAME') ;?>
			</th>
			<th width="5%">
				<?php echo JText::_('COM_map_PUBLISHED'); ?>
			</th>
			<th width="2%">
				<?php echo JText::_('COM_map_ID'); ?>
			</th>
		</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="5">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php if (!empty($this->items)) : ?>
				<?php foreach ($this->items as $i => $row) : ?>

					<tr>
						<td>
							<?php echo $this->pagination->getRowOffset($i); ?>
						</td>
						<td>
							<?php echo JHtml::_('grid.id', $i, $row->id); ?>
						</td>
						<td>
							<?php echo $row->greeting; ?>
						</td>
						<td align="center">
							<?php echo JHtml::_('jgrid.published', $row->published, $i, 'maps.', true, 'cb'); ?>
						</td>
						<td align="center">
							<?php echo $row->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</form>
