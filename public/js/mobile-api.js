// Helper global para consumir la API /api/v1 (Definición inmediata)
window.mobileApi = {
    request: function(endpoint, method = 'GET', data = null) {
        return new Promise((resolve, reject) => {
            const token = localStorage.getItem('jwt_token');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            const headers = {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            };

            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken;
            }
            if (token) {
                headers['Authorization'] = 'Bearer ' + token;
            }

            if (typeof $ !== 'undefined' && $.ajax) {
                $.ajax({
                    url: '/api/v1' + endpoint,
                    type: method,
                    headers: headers,
                    data: data ? JSON.stringify(data) : null,
                    contentType: 'application/json',
                    success: function(response) {
                        resolve(response);
                    },
                    error: function(xhr) {
                        if (xhr.status === 401) {
                            window.location.href = '/login';
                        }
                        let errorMessage = 'Error en la petición';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        reject(new Error(errorMessage));
                    }
                });
            } else {
                // Native fetch fallback
                const options = {
                    method: method,
                    headers: headers,
                    credentials: 'same-origin'
                };
                if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
                    options.body = JSON.stringify(data);
                }

                fetch('/api/v1' + endpoint, options)
                    .then(async res => {
                        if (res.status === 401) {
                            window.location.href = '/login';
                            throw new Error('No autorizado');
                        }
                        const json = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            throw new Error(json.message || `Error status ${res.status}`);
                        }
                        return json;
                    })
                    .then(resolve)
                    .catch(reject);
            }
        });
    },

    logout: function() {
        localStorage.removeItem('jwt_token');
        window.location.href = '/logout';
    }
};

// Document ready listeners
if (typeof $ !== 'undefined') {
    $(document).ready(function() {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        if ($.ajaxSetup) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                }
            });
        }

        $('#btn-logout-mobile').on('click', function(e) {
            e.preventDefault();
            if(confirm('¿Seguro que deseas cerrar sesión?')) {
                window.mobileApi.logout();
            }
        });
    });
}
