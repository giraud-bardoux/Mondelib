<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _updownvote.tpl 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>
<?php if (!empty($_SESSION['fromActivityFeed'])) {
	return;
} ?>
<?php $isPageSubject = empty($this->isPageSubject) ? $this->viewer() : $this->isPageSubject;
$item = $this->item;
$isVote = Engine_Api::_()->getDbTable('voteupdowns', 'comment')->isVote(array('resource_id' => $item->getIdentity(), 'resource_type' => $item->getType(), 'user_id' => $isPageSubject->getIdentity(), 'user_type' => $isPageSubject->getType()));
?>
<?php if ($this->viewer()->getIdentity()) { ?>
	<li class="feed_votebtn">
		<div class="d-flex align-items-center justify-content-center">
			<span class="upvote">
				<a href="javascript:;" data-itemguid="<?php echo $item->getGuid(); ?>" data-userguid="<?php echo $isPageSubject->getGuid(); ?>" title="<?php echo $this->translate('Up Vote'); ?>"
					class="<?php echo !empty($isVote) && $isVote->type == "upvote" ? '_disabled ' : ""; ?> activity_upvote_btn">
					<i><svg viewBox="0 0 24 24"><path d="M12.99,24h-1.98c-2.21,0-4.01-1.78-4.01-3.97v-7.03h-2.06c-1.23,0-2.28-.71-2.75-1.85-.47-1.13-.22-2.38,.65-3.25L9.17,1.18c1.57-1.57,4.09-1.57,5.64-.02,0,0,6.37,6.77,6.37,6.77,.85,.84,1.1,2.09,.63,3.22-.47,1.13-1.52,1.84-2.74,1.85h-2.06v7.03c0,2.19-1.8,3.97-4.01,3.97Zm-.99-22c-.51,0-1.01,.19-1.4,.58l-6.33,6.72c-.45,.45-.29,.95-.24,1.09,.06,.14,.3,.61,.9,.61h3.05c.55,0,1,.45,1,1v8.03c0,1.09,.9,1.97,2.01,1.97h1.98c1.11,0,2.01-.89,2.01-1.97V12c0-.55,.45-1,1-1h3.06c.6,0,.84-.47,.9-.61,.06-.14,.22-.64-.21-1.07L13.39,2.57c-.38-.38-.88-.57-1.38-.57Z"/></svg></i>
					<span><?php echo $item->vote_up_count; ?></span>
				</a>
			</span>
			<span class="downvote">
				<a href="javascript:;" data-itemguid="<?php echo $item->getGuid(); ?>"
					data-userguid="<?php echo $isPageSubject->getGuid(); ?>" title="<?php echo $this->translate('Down Vote'); ?>"
					class="<?php echo !empty($isVote) && $isVote->type == "downvote" ? '_disabled ' : ""; ?> activity_downvote_btn">
					<i><svg viewBox="0 0 24 24"><path d="M21.81,12.85c-.47-1.13-1.52-1.84-2.75-1.85h-2.06V3.97c0-2.19-1.8-3.97-4.01-3.97h-1.98c-2.21,0-4.01,1.78-4.01,3.97v7.03h-2.06c-1.23,0-2.28,.71-2.75,1.85-.47,1.13-.22,2.38,.63,3.22l6.37,6.77c.77,.77,1.79,1.16,2.81,1.16s2.03-.39,2.81-1.16c0,0,6.35-6.75,6.35-6.75,.87-.87,1.12-2.11,.65-3.24Zm-2.08,1.85l-6.34,6.73c-.77,.76-2.01,.75-2.76,.01l-6.37-6.77c-.43-.43-.27-.93-.21-1.07,.06-.14,.3-.61,.9-.61h3.06c.55,0,1-.45,1-1V3.97c0-1.09,.9-1.97,2.01-1.97h1.98c1.11,0,2.01,.89,2.01,1.97V12c0,.55,.45,1,1,1h3.05c.61,0,.84,.47,.9,.61,.06,.14,.22,.64-.24,1.09Z"/></svg></i>
					<span><?php echo $item->vote_down_count; ?></span>
				</a>
			</span>
		</div>
	</li>
<?php } ?>