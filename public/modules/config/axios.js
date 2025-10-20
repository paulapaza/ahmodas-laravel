window.api = axios.create({
  baseURL: window.location.origin,
  timeout: 20000,
  headers: {
    "X-Requested-With": "XMLHttpRequest",
  },
});

const csrfToken = document.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
  window.api.defaults.headers.common["X-CSRF-TOKEN"] = csrfToken.getAttribute("content");
}

window.api.interceptors.response.use(
  function (response) {
    return response.data;
  },
  function (error) {
    console.error("*** Error de Axios: ***", error.response || error);
    return Promise.reject(error);
  }
);
