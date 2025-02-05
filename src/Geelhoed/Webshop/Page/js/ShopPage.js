'use strict';

window.addEventListener("DOMContentLoaded", () =>
{
    function getSelectedOption(productId, option)
    {
        const optionElements = document.getElementsByClassName(productId + '-' + option);
        for (const optionElement of optionElements)
        {
            if (optionElement.checked)
            {
                return optionElement.value;
            }
        }

        return null;
    }

    function getOptions(button)
    {
        const optionsString = button.getAttribute('data-product-options');
        return optionsString ? optionsString.split(",") : [];
    }

    function hasAllOptionsSelected(button)
    {
        const productId = button.getAttribute('data-product-id');
        const options = getOptions(button);
        for (let option of options)
        {
            const selectedOption = getSelectedOption(productId, option)
            if (selectedOption === null)
            {
                return false;
            }
        }

        return true;
    }

    const addToCartButtons = document.getElementsByClassName('addToCart');
    for (let i = 0; i < addToCartButtons.length; i++)
    {
        const addToCartButton = addToCartButtons[i];
        addToCartButton.addEventListener('click', function()
        {
            const hash = addToCartButton.getAttribute('data-hash');
            const currency = addToCartButton.getAttribute('data-currency');
            const productId = addToCartButton.getAttribute('data-product-id');
            const options = getOptions(addToCartButton);
            const optionsObject = {};

            for (const option of options)
            {
                const selectedOption = getSelectedOption(productId, option)
                if (selectedOption === null)
                {
                    alert('Optie niet geselecteerd!');
                    return;
                }

                optionsObject[option] = selectedOption;
            }

            const payload = new FormData();
            payload.append('hash', hash);
            payload.append('currency', currency);
            payload.append('productId', productId);
            payload.append('options', JSON.stringify(optionsObject));

            fetch('/api/webwinkel/add-to-cart', {
                method: 'POST',
                body: payload,
            }).then((response) => {
                if (response.ok)
                {
                    location.reload();
                }
                else
                {
                    response.json().then((body) => {
                        alert(body.error);
                    });
                }
            });
        });
    }

    setInterval(function()
    {
        for (const addToCartButton of addToCartButtons)
        {
            if (hasAllOptionsSelected(addToCartButton))
            {
                addToCartButton.disabled = false;
                addToCartButton.title = '';
            }
            else
            {
                addToCartButton.disabled = true;
                addToCartButton.title = 'Je moet nog opties selecteren!';
            }

        }
    }, 1000);

    const removeFromCartButtons = document.getElementsByClassName('remove-from-cart');
    for (const removeFromCartButton of removeFromCartButtons)
    {
        removeFromCartButton.addEventListener('click', function()
        {
            const orderItemId = removeFromCartButton.getAttribute('data-order-item-id');
            const hash = removeFromCartButton.getAttribute('data-hash');

            const payload = new FormData();
            payload.append('hash', hash);
            payload.append('orderItemId', orderItemId);

            fetch('/api/webwinkel/remove-from-cart', {
                method: 'POST',
                body: payload,
            }).then((response) => {
                if (response.ok)
                {
                    location.reload();
                }
                else
                {
                    response.json().then((body) => {
                        alert(body.error);
                    });
                }
            });
        });


    }
});
