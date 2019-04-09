{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}
{if $outputMode neq 'print'}
  {include file="CRM/common/crmeditable.tpl"}
{/if}
{* include preprocess form for 'create report buttons' query is executed in postprocess, so without buttons (and so without post) there is no postprocess *}

{* this div is being used to apply special css *}
    {if $section eq 1}
    <div class="crm-block crm-content-block crm-report-layoutGraph-form-block">
        {*include the graph*}
        {include file="CRM/Report/Form/Layout/Graph.tpl"}
    </div>
    {elseif $section eq 2}
    <div class="crm-block crm-content-block crm-report-layoutTable-form-block">
        {*include the table layout*}
        {include file="CRM/Report/Form/Layout/Table.tpl"}
  </div>
    {else}
    {if $criteriaForm OR $instanceForm OR $instanceFormError}
    <div class="crm-block crm-form-block crm-report-field-form-block">
        {include file="CRM/Report/Form/Fields.tpl"}
    </div>
    {/if}

    <div class="crm-block crm-content-block crm-report-form-block">
        {*include actions*}
        {include file="CRM/Report/Form/Actions.tpl"}

        {*Statistics at the Top of the page*}
        {include file="CRM/Report/Form/Statistics.tpl" top=true}

        {*include the graph*}
        {include file="CRM/Report/Form/Layout/Graph.tpl"}

        {*include actions*}
        {if $rows}
          {$anonymize}
        {/if}

        {if $rows}

            {if $pager and $pager->_response and $pager->_response.numPages > 1}
                <div class="report-pager">
                    {include file="CRM/common/pager.tpl" location="top" noForm=0}
                </div>
            {/if}
            {literal}
              <script>
                cj(document).ready(function(){
                  cj('#AnonymizeExitUsers input[id=chkSelectAll]').click(function(){
                    if(cj('#AnonymizeExitUsers input[id=chkSelectAll]').prop('checked') == false) {
                      cj('#AnonymizeExitUsers input[class=chkUser]').prop('checked',false);
                    }
                    if(cj('#AnonymizeExitUsers input[id=chkSelectAll]').prop('checked') == true) {
                      cj('#AnonymizeExitUsers input[class=chkUser]').prop('checked',true);
                    }
                  });
                });
              </script>
            {/literal}

            <table class="report-layout display">
                {capture assign="tableHeader"}
                    {foreach from=$columnHeaders item=header key=field}

                        {assign var=class value=""}
                        {if $header.type eq 1024 OR $header.type eq 1 OR $header.type eq 512}
                        {assign var=class value="class='reports-header-right'"}
                        {else}
                            {assign var=class value="class='reports-header'"}
                        {/if}
                        {if !$skip}
                           {if $header.colspan}
                               <th colspan={$header.colspan}>{$header.title}</th>
                              {assign var=skip value=true}
                              {assign var=skipCount value=`$header.colspan`}
                              {assign var=skipMade  value=1}
                           {else}
                               <th {$class}>{$header.title}</th>
                           {assign var=skip value=false}
                           {/if}
                        {else} {* for skip case *}
                           {assign var=skipMade value=`$skipMade+1`}
                           {if $skipMade >= $skipCount}{assign var=skip value=false}{/if}
                        {/if}
                    {/foreach}
                {/capture}

                {if !$sections} {* section headers and sticky headers aren't playing nice yet *}
                    <thead class="sticky">
                    <tr><td><input type="checkbox" id="chkSelectAll" name="chkSelectAll"/></td></td>
                        {$tableHeader}
                </tr>
                </thead>
                {/if}

                {* pre-compile section header here, rather than doing it every time under foreach *}
                {capture assign=sectionHeaderTemplate}
                    {assign var=columnCount value=$columnHeaders|@count}
                    {assign var=l value=$smarty.ldelim}
                    {assign var=r value=$smarty.rdelim}
                    {foreach from=$sections item=section key=column name=sections}
                        {counter assign="h"}
                        {$l}isValueChange value=$row.{$column} key="{$column}" assign=isValueChanged{$r}
                        {$l}if $isValueChanged{$r}

                            {$l}if $sections.{$column}.type & 4{$r}
                                {$l}assign var=printValue value=$row.{$column}|crmDate{$r}
                            {$l}elseif $sections.{$column}.type eq 1024{$r}
                                {$l}assign var=printValue value=$row.{$column}|crmMoney{$r}
                            {$l}else{$r}
                                {$l}assign var=printValue value=$row.{$column}{$r}
                            {$l}/if{$r}

                            <tr><th colspan="{$columnCount}">
                                <h{$h}>{$section.title}: {$l}$printValue|default:"<em>none</em>"{$r}
                                    ({$l}sectionTotal key=$row.{$column} depth={$smarty.foreach.sections.index}{$r})
                                </h{$h}>
                            </th></tr>
                            {if $smarty.foreach.sections.last}
                                <td><input type="checkbox" id="chkSelectAll" name="chkSelectAll"/></td></td>
                                <tr>{$l}$tableHeader{$r}</tr>
                            {/if}
                        {$l}/if{$r}
                    {/foreach}
                {/capture}

                {foreach from=$rows item=row key=rowid}
                   {eval var=$sectionHeaderTemplate}
                    <tr  class="{cycle values="odd-row,even-row"} {$row.class} crm-report" id="crm-report_{$rowid}">
                        {foreach from=$columnHeaders item=header key=field}
                            {if $field eq 'contact_id'}<td><input type="checkbox" id="chkUser_{$row.contact_id|strip_tags|trim}" name="chkUser_{$row.contact_id|strip_tags|trim}" class="chkUser"/></td>{/if}
                            {assign var=fieldLink value=$field|cat:"_link"}
                            {assign var=fieldHover value=$field|cat:"_hover"}
                            <td class="crm-report-{$field}{if $header.type eq 1024 OR $header.type eq 1 OR $header.type eq 512} report-contents-right{elseif $row.$field eq 'Subtotal'} report-label{/if}">
                                {if $row.$fieldLink}
                                    <a title="{$row.$fieldHover}" href="{$row.$fieldLink}">
                                {/if}

                                {if $row.$field eq 'Subtotal'}
                                    {$row.$field}
                                {elseif $header.type & 4 OR $header.type & 256}
                                    {if $header.group_by eq 'MONTH' or $header.group_by eq 'QUARTER'}
                                        {$row.$field|crmDate:$config->dateformatPartial}
                                    {elseif $header.group_by eq 'YEAR'}
                                        {$row.$field|crmDate:$config->dateformatYear}
                                    {else}
                                        {if $header.type & 4}
                                           {$row.$field|truncate:10:''|crmDate}
                                        {else}
                                           {$row.$field|crmDate}
                                        {/if}
                                    {/if}
                                {elseif $header.type eq 1024}
                                    {if $currencyColumn}
                                        <span class="nowrap">{$row.$field|crmMoney:$row.$currencyColumn}</span>
                                    {else}
                                        <span class="nowrap">{$row.$field|crmMoney}</span>
                                   {/if}
                                {elseif $field eq 'contact_id'}
                                  <a href="/civicrm/contact/view?reset=1&cid={$row.contact_id}" target="_blank">{$row.contact_id}</a>
                                {else}
                                    {$row.$field}
                                {/if}

                                {if $row.$fieldLink}</a>{/if}
                            </td>
                        {/foreach}
                    </tr>
                {/foreach}

                {if $grandStat}
                    {* foreach from=$grandStat item=row*}
                    <tr class="total-row">
                        {foreach from=$columnHeaders item=header key=field}
                            <td class="report-label">
                                {if $header.type eq 1024}
                                    {$grandStat.$field|crmMoney}
                                {else}
                                    {$grandStat.$field}
                                {/if}
                            </td>
                        {/foreach}
                    </tr>
                    {* /foreach*}
                {/if}
            </table>
            {if $pager and $pager->_response and $pager->_response.numPages > 1}
                <div class="report-pager">
                    {include file="CRM/common/pager.tpl"  noForm=0}
                </div>
            {/if}

    </div>
    {/if}
    {if $outputMode == 'print'}
      <script type="text/javascript">
        window.print();
      </script>
    {/if}

      <br />
        {*Statistics at the bottom of the page*}
        {include file="CRM/Report/Form/Statistics.tpl" bottom=true}

        {include file="CRM/Report/Form/ErrorMessage.tpl"}
    </div>
    {/if}
    {if $outputMode == 'print'}
      <script type="text/javascript">
        window.print();
      </script>
    {/if}
