<?php
/**
 * Photoblur Module
 *
 * @category   Application_Extensions
 * @package    Photoblur
 */
?>

<h2><?php echo $this->translate("Photo Blur Plugin") ?></h2>

<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
  </div>
<?php endif; ?>

<div class="admin_statistics">
  <h3><?php echo $this->translate("Statistics") ?></h3>
  <ul>
    <li>
      <?php echo $this->translate("Total Blurred Photos:") ?>
      <span class="admin_statistics_value"><?php echo $this->totalBlurs ?></span>
    </li>
    <li>
      <?php echo $this->translate("Unique Users:") ?>
      <span class="admin_statistics_value"><?php echo $this->uniqueUsers ?></span>
    </li>
    <li>
      <?php echo $this->translate("Blurred Today:") ?>
      <span class="admin_statistics_value"><?php echo $this->todayBlurs ?></span>
    </li>
  </ul>
</div>

<br />

<div>
  <h3><?php echo $this->translate("Recent Blurred Photos") ?></h3>
  
  <?php if( count($this->paginator) ): ?>
    <table class='admin_table'>
      <thead>
        <tr>
          <th><?php echo $this->translate("ID") ?></th>
          <th><?php echo $this->translate("User") ?></th>
          <th><?php echo $this->translate("Photo") ?></th>
          <th><?php echo $this->translate("Blur Level") ?></th>
          <th><?php echo $this->translate("Date") ?></th>
          <th><?php echo $this->translate("Options") ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($this->paginator as $item): ?>
          <?php $user = Engine_Api::_()->getItem('user', $item->user_id); ?>
          <?php $photo = Engine_Api::_()->getItem('album_photo', $item->photo_id); ?>
          <tr>
            <td><?php echo $item->blur_id ?></td>
            <td>
              <?php if($user): ?>
                <?php echo $this->htmlLink($user->getHref(), $user->getTitle(), array('target' => '_blank')) ?>
              <?php else: ?>
                <?php echo $this->translate('Deleted User') ?>
              <?php endif; ?>
            </td>
            <td>
              <?php if($photo): ?>
                <?php echo $this->htmlLink($photo->getHref(), $this->translate('View Photo'), array('target' => '_blank')) ?>
              <?php else: ?>
                <?php echo $this->translate('Deleted Photo') ?>
              <?php endif; ?>
            </td>
            <td><?php echo $item->blur_level ?></td>
            <td><?php echo $this->locale()->toDateTime($item->creation_date) ?></td>
            <td>
              <a href="<?php echo $item->getHref(); ?>" target="_blank">
                <?php echo $this->translate('View Blurred') ?>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <br />
    <?php echo $this->paginationControl($this->paginator); ?>
  <?php else: ?>
    <div class="tip">
      <span><?php echo $this->translate("No photos have been blurred yet.") ?></span>
    </div>
  <?php endif; ?>
</div>