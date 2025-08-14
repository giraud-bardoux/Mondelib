
<?php

  // if( $this->blog && $this->blog->photo_id ):
  //   // blog photo
  //   echo $this->htmlLink($this->blog->getHref(),
  //   $this->itemPhoto($this->blog),
  //   array('class' => $photoClass));

  //   $ownerPhoto = $this->itemBackgroundPhoto($this->owner, 'thumb.icon');
  //   $photoClass = 'blogs_owner_icon';
  // endif;

  //if( !isset($ownerPhoto) ):
    $ownerPhoto = $this->itemBackgroundPhoto($this->owner);
  //endif;
?>

<div class="blog_gutter_owner">
  <div class="blog_gutter_owner_photo">
    <?php echo $this->htmlLink($this->owner->getHref(), $ownerPhoto); ?>
  </div>
  <div class="blog_gutter_owner_name">
    <?php  echo $this->htmlLink($this->owner->getHref(), $this->owner->getTitle()); ?>
  </div>
</div>