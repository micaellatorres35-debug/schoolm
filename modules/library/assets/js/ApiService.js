'use strict';
class ApiService {
    #endpoint = 'api.php';

    #csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    }

    async get(params) {
        const busted = { ...params, _t: Date.now() };
        const url = this.#endpoint + '?' + new URLSearchParams(busted);
        const res = await fetch(url, {
            credentials: 'same-origin',
            cache: 'no-store',
        });
        return res.json();
    }

    async post(action, data) {
        const res = await fetch(`${this.#endpoint}?action=${action}`, {
            method:      'POST',
            credentials: 'same-origin',
            cache:       'no-store',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': this.#csrf(),
            },
            body: JSON.stringify(data),
        });
        return res.json();
    }

    async upload(formData) {
        const res = await fetch(this.#endpoint, {
            method:      'POST',
            credentials: 'same-origin',
            cache:       'no-store',
            headers:     { 'X-CSRF-Token': this.#csrf() },
            body:        formData,
        });
        return res.json();
    }
}