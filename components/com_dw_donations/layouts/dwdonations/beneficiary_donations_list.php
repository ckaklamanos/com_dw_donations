<?php
defined('JPATH_BASE') or die;

$items=$displayData['items'];
$pagination=$displayData['pagination'];
$total=$displayData['total'];

?>

<div class="uk-align-right uk-margin-small-top">
<?php echo JLayoutHelper::render( 
	'export.items', 
	array ( 
		'items' => $items , 
		'component' => 'com_dw_donations' , 
		'fields' => 'id,fname,lname,email,amount,created,order_code' ,
		'filename' => 'donorwiz_donations_'.JFactory::getUser() -> name.'_'.JFactory::getDate()->format('d M Y') 
	) , 
	JPATH_ROOT .'/components/com_donorwiz/layouts' , 
	null 
); ?>
</div>

<h1>

<?php if (count($items)) :?>
	<?php echo JText::_('COM_DW_DONATIONS_LIST_DONATIONS'); ?>
<?php else :?>
	<?php echo JText::_('COM_DW_DONATIONS_LIST_NO_DONATIONS'); ?>
<?php endif;?>

</h1>

<?php if (count($items)) :?>
	<div class="uk-text-right uk-text-extra-large">
		<?php echo JText::_('COM_DW_DONATIONS_LIST_DONATIONS_TOTAL').': <span class="uk-text-primary">€'.$total.'</span>'; ?>
	</div>
	<hr>
<?php endif;?>

<?php if (count($items)) :?>

<ul class="uk-list uk-list-line">

<?php foreach($items as $k=>$item) : ?>

<?php if ( $item->state!=1 ) continue; ?>

<?php 	
	$item->currency_sign = '€';
?>

<li class="uk-panel uk-panel-box uk-panel-blank uk-panel-border uk-panel-shadow" >
	<div class="uk-grid">
	
		<div class="uk-width-3-4">
			<div class="uk-width-1-1 uk-text-large">
				
				<?php echo $item->lname.' '.$item->fname; ?>

			</div>

			<div class="uk-width-1-1">
				<span class="uk-display-small-block uk-width-small-1-1">
					<i class="uk-icon-calendar"></i>
					<?php echo JFactory::getDate( $item->modified )->format('D, d M Y'); ?>
				</span>
				<i class="uk-icon-clock-o uk-margin-small-left"></i>
				<?php echo JFactory::getDate( $item->modified )->format('h:m'); ?> 
				<i class="uk-icon-map-marker uk-margin-small-left"></i>
				<?php echo $item->country; ?>
			</div>

				
			<div class="uk-width-1-1">
					
					<i class="uk-icon-envelope-o uk-text-primary"></i>
					<a href="mailto:<?php echo $item->email;?>">
					<?php echo JText::_('COM_DW_DONATIONS_LIST_SEND_MAIL'); ?>
					</a>
			</div>

			<div class="uk-width-1-1">
				<?php if( $item->donor_id && JUser::getTable()->load($item->donor_id)):?>
					<i class="uk-icon-user uk-text-primary"></i>
					<a target="_blank" href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$item->donor_id);?>">
					<?php echo JText::_('COM_DW_DONATIONS_LIST_VIEW_PROFILE'); ?>
					</a>
				<?php endif; ?>
				</div>
				
			<?php if( $item -> anonymous == 1) : ?>				
				<div class="uk-text-muted">
					 <?php echo JText::_('COM_DW_DONATIONS_LIST_ANONYMOUS_TEXT');?>
				</div>
			<?php endif;?>
		</div>

		<div class="uk-width-1-4 uk-text-right uk-text-extra-large uk-text-medium-small">
			<?php echo $item->currency_sign.$item->amount; ?>
		</div>
	
	</div>
</li>
		
<?php endforeach;?>

</ul>

<?php echo $pagination->getPagesLinks(); ?>

<?php endif;?>