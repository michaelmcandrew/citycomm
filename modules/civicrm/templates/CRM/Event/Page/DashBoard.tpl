{* CiviEvent DashBoard (launch page) *}
{capture assign=newEventURL}{crmURL p="civicrm/event/manage" q="action=add&reset=1"}{/capture}
{capture assign=configPagesURL}{crmURL p="civicrm/event/manage" q="reset=1"}{/capture}
{capture assign=icalFile}{crmURL p='civicrm/event/ical' q="reset=1" fe=1 a=1}{/capture}
{capture assign=icalFeed}{crmURL p='civicrm/event/ical' q="reset=1&page=1" fe=1 a=1}{/capture}
{capture assign=rssFeed}{crmURL p='civicrm/event/ical' q="reset=1&page=1&rss=1" fe=1 a=1}{/capture}
{capture assign=htmlFeed}{crmURL p='civicrm/event/ical' q="reset=1&page=1&html=1" fe=1 a=1}{/capture}

{if $eventSummary.total_events}
    {if $eventAdmin}
    <div class="float-right">
    <table class="form-layout-compressed">
    <tr>
        <td><a href="{$configPagesURL}" class="button"><span>&raquo; {ts}Manage Events{/ts}</span></a></td>
        <td><a href="{$newEventURL}" class="button"><span>&raquo; {ts}New Event{/ts}</span></a></td>
    </tr>
    </table>
    </div>
    {/if}
    <h3>{ts}Event Summary{/ts}  {help id="id-event-intro"}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="{$htmlFeed}" title="{ts}HTML listing of current and future public events.{/ts}"><img src="{$config->resourceBase}i/applications-internet.png" alt="{ts}HTML listing of current and future public events.{/ts}" /></a>&nbsp;&nbsp;<a href="{$rssFeed}" title="{ts}Get RSS 2.0 feed for current and future public events.{/ts}"><img src="{$config->resourceBase}i/feed-icon.png" alt="{ts}Get RSS 2.0 feed for current and future public events.{/ts}" /></a>&nbsp;&nbsp;<a href="{$icalFile}" title="{ts}Download iCalendar file for current and future public events.{/ts}"><img src="{$config->resourceBase}i/office-calendar.png" alt="{ts}Download iCalendar file for current and future public events.{/ts}" /></a>&nbsp;&nbsp;<a href="{$icalFeed}" title="{ts}Get iCalendar feed for current and future public events.{/ts}"><img src="{$config->resourceBase}i/ical_feed.gif" alt="{ts}Get iCalendar feed for current and future public events.{/ts}" /></a></h3>
    <table class="report">
    <tr class="columnheader-dark">
        <th scope="col">{ts}Event{/ts}</th>
        <th scope="col">{ts}ID{/ts}</th>
        <th scope="col">{ts}Type{/ts}</th>
        <th scope="col">{ts}Public{/ts}</th>
        <th scope="col">{ts}Date(s){/ts}</th>
        <th scope="col">{ts}Participants{/ts}</th>
        {if $eventAdmin or $eventMap}
            <th></th>
        {/if}
    </tr>
    {foreach from=$eventSummary.events item=values key=id}
    <tr>
        <td><a href="{crmURL p="civicrm/event/info" q="reset=1&id=`$id`"}" title="{ts}View event info page"{/ts}>{$values.eventTitle}</a></td>
        <td>{$id}</td>
        <td>{$values.eventType}</td>
        <td>{$values.isPublic}</td>
        <td class="nowrap">{$values.startDate}&nbsp;{if $values.endDate}to{/if}&nbsp;{$values.endDate}</td>
        <td class="right">
           {if $values.participants_url and $values.participants}<a href="{$values.participants_url}" title="{ts 1=$eventSummary.statusDisplay}List %1 participants{/ts}">{$eventSummary.statusDisplay}:&nbsp;{$values.participants}</a><hr />{else}{$eventSummary.statusDisplay}:&nbsp;{$values.participants}<hr />{/if}
           {if $values.pending_url and $values.pending}<a href="{$values.pending_url}" title="{ts 1=$eventSummary.statusDisplayPending}List %1 participants{/ts}">{$eventSummary.statusDisplayPending}:&nbsp;{$values.pending}</a><hr />{else}{$eventSummary.statusDisplayPending}:&nbsp;{$values.pending}<hr />{/if}
           {if $values.maxParticipants}{ts 1=$values.maxParticipants}(max %1){/ts}{/if}
        </td>
        {if $eventAdmin or $eventMap}
            <td>
            {if $values.isMap}
            <a href="{$values.isMap}" title="{ts}Map event location{/ts}">&raquo;&nbsp;{ts}Map{/ts}</a>&nbsp;|&nbsp;
            {/if}
            {if $eventAdmin}
                <a href="{$values.configure}" title="{ts}Configure event information, fees, discounts, online registration...{/ts}">&raquo;&nbsp;{ts}Configure{/ts}</a>
            {/if}
        {/if}
        </td>
    </tr>
    {/foreach}

    {if $eventSummary.total_events GT 10}
        <tr>
            <td colspan="7"><a href="{crmURL p='civicrm/admin/event' q='reset=1'}">&raquo; {ts}Browse more events{/ts}...</a></td>
        </tr>
    {/if}
    </table>
{else}
    <br />
    <div class="messages status">
      <dl>
      <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}" /></dt>      
      <dd>
        {ts}There are no active Events to display.{/ts}
        {if $eventAdmin}
            {ts 1=$newEventURL}You can <a href="%1">Create a New Event</a> now.{/ts}
        {/if}
      </dd>
      </dl>
    </div>
{/if}

{if $pager->_totalItems}
    <h3>{ts}Recent Registrations{/ts}</h3>
    <div class="form-item">
        {include file="CRM/Event/Form/Selector.tpl" context="event_dashboard"}
    </div>
{/if}
