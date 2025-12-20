import ky from 'ky'

const url = import.meta.env.VITE_URL

export const useGet = async (uri, pending, value, searchParams = null) => {
    const mainUrl = `${ url }/${ uri }`
    pending.value = true
    await
        ky.get(mainUrl,
            {
                searchParams : searchParams,
                timeout : 60000,
            }).json()
            .then(response => {
                value.value = response
            })
            .then(() => {
                pending.value = false
            })
}