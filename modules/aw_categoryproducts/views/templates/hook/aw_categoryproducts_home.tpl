<section>
  <div class="row products">
    {foreach $blocks_variables as $block_variables}
        <div class="single-block col-md-4">
            <div class="single-block-header col-md-12 mb-1">
                <h2 class="title block-category-title">{$block_variables.category_info.name}</h2>
            </div>
        {foreach $block_variables.products as $product}
            <div class="col-md-12 product-line mb-2">
            <img src="{$product.cover.small.url}">
            <span class="product-title">{$product.name}</span>
            <div class="product-actions js-product-actions">
            <div class="product-price">{$product.price}</div>
              {block name='product_buy'}
                <form action="{$urls.pages.cart}" method="post" id="add-to-cart-or-refresh">
                  <input type="hidden" name="token" value="{$static_token}">
                  <input type="hidden" name="id_product" value="{$product.id}" id="product_page_product_id">
                    <div class="product-quantity">
                    <div class="qty">
                        <input
                            class="input-group bootstrap-touchspin"
                            type="number"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            value="1"
                            min='1'
                            max="{$product.quantity}"
                            name="product-quantity-spin"
                            aria-label="{l s='%productName% product quantity field' sprintf=['%productName%' => $product.name] d='Shop.Theme.Checkout'}"
                        />
                    </div>
                        <div class="add">
                        <button
                            class="btn btn-primary add-to-cart"
                            data-button-action="add-to-cart"
                            type="submit"
                            {if !$product.add_to_cart_url}
                            disabled
                            {/if}
                        >
                            <i class="material-icons shopping-cart">&#xE547;</i>
                        </button>
                        </div>
                    </div>
                </form>
              {/block}
              </div>
            </div>
         {/foreach}
         <div class="col-md-12 text-xs-center">
            <a href="{$link->getCategoryLink($block_variables.category_info.id_category)}" class="link-to-category">{l s="Więcej z tej kategorii" d="Modules.Aw_categoryproducts.Shop'}"}</a>
         </div>
         </div>
    {/foreach}
  </div>
</section>
