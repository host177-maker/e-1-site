import ky from 'ky'

const url = '/order'

export const usePost = async (uri, formData, answer) => {
    const mainUrl = `${ url }/${ uri }`
    await
        ky.post(mainUrl,
            {body: formData}).json()
            .then(response => {
                if (response.success) {
                    if(response.ecom_purchase_js_object){
                        dataLayer.push({ecommerce: null});
                        window.dataLayer = window.dataLayer || [];
                        dataLayer.push({
                            event: "purchase",
                            ecommerce: JSON.parse(response.ecom_purchase_js_object),
                        });
                    }
                    window.location = '/order/success/?ORDER_ID=' + response.orderId;
                  } else {
                    answer.value = response
                  }              
            })
}