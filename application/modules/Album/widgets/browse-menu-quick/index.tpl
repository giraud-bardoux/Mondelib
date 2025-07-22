<?php if( engine_count($this->quickNavigation) > 0 ): ?>
  <div class="sidebar_links">
    <?php
      // Render the menu
      echo $this->navigation()
        ->menu()
        ->setContainer($this->quickNavigation)
        ->render();
    ?>
  </div>
<?php endif; ?>
