
<ul class="nav nav-pills">
    <li class="<?php echo $this->route === 'resNotice/index' ? 'active' : ''  ?>">
        <a href="<?php echo $this->createUrl('/resNotice/index'); ?>">Response</a>
    </li>
    <li class="<?php echo $this->route === 'preRisk/index' ? 'active' : ''  ?>">
        <a href="<?php echo $this->createUrl('/preRisk/index'); ?>">Pre Risk</a>
    </li>
    <li class="<?php echo $this->route === 'fsReport/index' ? 'active' : ''  ?>">
        <a href="<?php echo $this->createUrl('/fsReport/index'); ?>">Fire Shield</a>
    </li>
    <li class="<?php echo $this->route === 'engEngines/indexAnalytics' ? 'active' : ''  ?>">
        <a href="<?php echo $this->createUrl('/engEngines/indexAnalytics'); ?>">Engines</a>
    </li>
    <li class="<?php echo $this->route === 'resDedicated/index' || $this->route === 'resDedicated/indexAllClients' ? 'active' : ''  ?>">
        <a href="<?php echo $this->createUrl('/resDedicated/index'); ?>">Dedicated</a>
    </li>
</ul>