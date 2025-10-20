// store/user.js
window.userModule = {
  namespaced: true,
  state: {
    nombre: 'Rakhel'
  },
  mutations: {
    setNombre(state, nuevo) {
      state.nombre = nuevo
    }
  }
}
