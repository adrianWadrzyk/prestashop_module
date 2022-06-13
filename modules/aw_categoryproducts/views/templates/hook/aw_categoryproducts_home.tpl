{if isset($blocks_variables)}
  <section>
    <div class="row products aw-category-products">
      {foreach $blocks_variables as $block_variables}
        <div class="single-block col-xl-4 col-md-6 mb-2">
          <div class="single-block-header col-md-12 mb-2">
            <h2 class="title block-category-title">{$block_variables.category_info.name}</h2>
            <div class="custom-container-arrow-slick">
              <span class="arrow_back arrow"></span>
              <span class="arrow_next arrow"></span>
            </div>
          </div>
          <div class="vertical-slick-carousel col-md-12">
            {foreach $block_variables.products as $product}
              {include file="module:aw_categoryproducts/views/templates/hook/aw_block_line.tpl" product=$product}
            {/foreach}
          </div>
          <div class="col-md-12 text-xs-center">
            <a href="{$link->getCategoryLink($block_variables.category_info.id_category)}"
              class="link-to-category">{l s="Więcej z tej kategorii" d="Modules.Aw_categoryproducts.Shop"}</a>
          </div>
        </div>
      {/foreach}
    </div>
  </section>
{/if}