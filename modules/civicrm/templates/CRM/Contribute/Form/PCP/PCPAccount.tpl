{* Displays account creation and supporter profile form (step 1 in creating a personal campaign page as well as Update Contact info). *}
{if $action EQ 1}
<div id="help">
        {ts}Creating your own fundraising page is simple. Fill in some basic information below, which will allow you to manage your page and invite friends to make a contribution.
        Then click 'Continue' to personalize and announce your page.{/ts}
</div>
{/if}

{if $profileDisplay}
<div class="messages status">
<dl>
  	<dt><img src="{$config->resourceBase}i/Eyeball.gif" alt="{ts}Profile{/ts}"/></dt>
    	<dd><p><strong>{ts}Profile is not configured with Email address.{/ts}</strong></p></dd>
</dl>
</div>
{else}
<div class="form-item">
{include file="CRM/common/CMSUser.tpl"} 
{include file="CRM/UF/Form/Block.tpl" fields=$fields} 
{if $isCaptcha} 
{include file='CRM/common/ReCAPTCHA.tpl'} 
{/if}
<dl>
	<dt></dt>
	<dd class="html-adjust">{$form.buttons.html}</dd>
</dl>
</div>
{/if}