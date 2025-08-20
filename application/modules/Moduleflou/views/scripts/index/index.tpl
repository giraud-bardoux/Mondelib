<div class="moduleflou-index">
  <h2>Démo du Helper Flou (source locale)</h2>
  <p>Miniature test avec <code>flouThumb</code> :</p>

  <?php
    // Utilise une image locale si disponible
    $url = $this->baseUrl('/flou.jpg');
    echo $this->flouThumb($url, 10, ['alt' => 'Miniature test']);
  ?>

  <p>Invité (non connecté) : flouté • Membre connecté : net.</p>
</div>
