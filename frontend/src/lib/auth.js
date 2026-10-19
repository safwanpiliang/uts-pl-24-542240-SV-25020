import { reactive } from 'vue'

const TOKEN_KEY = 'jadwalku.token'
const USER_KEY = 'jadwalku.user'

function read(key) {
  try {
    return localStorage.getItem(key)
  } catch {
    return null
  }
}

function parseUser() {
  try {
    return JSON.parse(read(USER_KEY))
  } catch {
    return null
  }
}

export const auth = reactive({
  token: read(TOKEN_KEY),
  user: parseUser(),
})

export function setSession(token, user) {
  auth.token = token
  auth.user = user
  localStorage.setItem(TOKEN_KEY, token)
  localStorage.setItem(USER_KEY, JSON.stringify(user))
}

export function clearSession() {
  auth.token = null
  auth.user = null
  localStorage.removeItem(TOKEN_KEY)
  localStorage.removeItem(USER_KEY)
}
