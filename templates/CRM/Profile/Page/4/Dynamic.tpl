{assign var=ad1 value="Address line 1"}
{assign var=ad2 value="Address line 2"}
{assign var=ad3 value="Address line 3"}
{assign var=city value="City"}
{assign var=pc value="Post code"}
{assign var=cg value="Client groups"}

{$row.Description}

<p><b>Website: </b>{$row.Website}</p>
<p><b>Email: </b>{$row.Email}</p>
<p><b>Phone: </b>{$row.Phone}</p>
<p><b>Address:</b>{if $row.$ad1|count_characters > 0}  {$row.$ad1}, {/if}

{if $row.$ad2|count_characters > 0}  {$row.$ad2}, {/if}
{if $row.$ad3|count_characters > 0}  {$row.$ad3}, {/if}
{if $row.$city|count_characters > 0}  {$row.$city}, {/if}
{if $row.$pc|count_characters > 0}  {$row.$pc}{/if}
</p>

<h3>Services</h3>
<p>{$row.Services}</p>

<h3>Client groups</h3>
<p>{$row.$cg}</p>

