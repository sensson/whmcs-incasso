<div id="content-padded">
    <ul class="nav nav-tabs admin-tabs" role="tablist">
    {foreach from=$pages key=name item=page}
        <li {if $name == $active_page}class="active"{/if}>
            <a href="{$modulelink}&amp;page={$name}&render=true">
                {$language.$name}
            </a>
        </li>
    {/foreach}
    </ul>
    <div class="tab-content admin-tabs">
        <div class="tab-pane active">
            {block name=content}{/block}
        </div>
    </div>
</div>