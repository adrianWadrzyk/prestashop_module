<div class="row">
              <div class="col-md-12 product-line mb-2">
              <a href={$product.url} class="product-url">
                <img src="{$product.cover.small.url}" class="product-img" alt="product miniature">
                <span class="product-title">{$product.name}</span>
              </a>
              <div class="product-actions js-product-actions">  
              <div class="product-price">{$product.price}</div>
   
              {block name='product_buy'}
                <form action="{$urls.pages.cart}" method="get" id="add-to-cart-or-refresh">
                  <input type="hidden" name="token" value="{$static_token}">
                  <input type="hidden" name="id_product" value="{$product.id}" id="product_page_product_id">
                  <input type="hidden" name="action" value="add-to-cart">
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
                            name="qty"
                            aria-label="{l s='%productName% product quantity field' sprintf=['%productName%' => $product.name] d='Shop.Theme.Checkout'}"
                        />
                    </div>
                        <div class="add">
                        <button
                            class="btn btn-primary add-to-cart ajax_add_to_cart_button "
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
            </div>