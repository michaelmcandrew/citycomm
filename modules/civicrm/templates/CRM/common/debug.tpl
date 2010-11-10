{if $smarty.get.smartyDebug}
{debug}
{/if}

{if $smarty.get.sessionReset}
{$session->reset($smarty.get.sessionReset)}
{/if}

{if $smarty.get.sessionDebug}
{$session->debug($smarty.get.sessionDebug)}
{/if}

{if $smarty.get.directoryCleanup} 
{$config->cleanup($smarty.get.directoryCleanup)}
{/if}

{if $smarty.get.cacheCleanup} 
{$config->clearDBCache()}
{/if}

{if $smarty.get.configReset} 
{$config->reset()}
{/if}
