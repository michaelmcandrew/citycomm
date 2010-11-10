{if $top}
    {if $printOnly}
        <h1>{$reportTitle}</h1>
        <div id="report-date">{$reportDate}</div>
    {/if}
    <br/>
    {if $statistics and $outputMode}
        <table class="report-layout">
            {foreach from=$statistics.groups item=row}
                <tr>
                   <th class="statistics" scope="row">{$row.title}</th>
                   <td>{$row.value}</td>
                </tr>
            {/foreach}
            {foreach from=$statistics.filters item=row}
                <tr>
                    <th class="statistics" scope="row">{$row.title}</th>
                    <td>{$row.value}</td>
                </tr>
            {/foreach}
        </table>
    {/if}
{/if}

{if $bottom and $rows and $statistics}
    <br/>
    <table class="report-layout">
        {foreach from=$statistics.counts item=row}
            <tr>
                <th class="statistics" scope="row">{$row.title}</th>
                <td>
                   {if $row.type eq 1024}
                       {$row.value|crmMoney}
                   {else}
                       {$row.value|crmNumberFormat}
                   {/if}

                </td>
            </tr>
        {/foreach}
    </table>
{/if}