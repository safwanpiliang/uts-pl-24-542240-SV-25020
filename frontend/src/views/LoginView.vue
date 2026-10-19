<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../lib/api'
import { setSession } from '../lib/auth'
import BrandLogo from '../components/BrandLogo.vue'
import Alert from '../components/ui/Alert.vue'
import Button from '../components/ui/Button.vue'
import Input from '../components/ui/Input.vue'
import { LetterIcon } from '@solar-icons/vue/bold-duotone/letter'
import { LockPasswordIcon } from '@solar-icons/vue/bold-duotone/lock-password'
import { DangerCircleIcon } from '@solar-icons/vue/bold-duotone/danger-circle'

const router = useRouter()

const email = ref('')
const password = ref('')
const errors = ref({})
const message = ref('')
const loading = ref(false)

async function submit() {
  errors.value = {}
  message.value = ''

  if (!email.value) errors.value.email = 'Email wajib diisi.'
  if (!password.value) errors.value.password = 'Password wajib diisi.'
  if (Object.keys(errors.value).length) return

  loading.value = true
  try {
    const { data } = await api.post('/login', { email: email.value, password: password.value })
    setSession(data.token, data.user)
    router.push({ name: 'schedules' })
  } catch (error) {
    const status = error.response?.status
    if (status === 401) {
      message.value = 'Email atau password salah.'
    } else if (status === 422) {
      const fieldErrors = error.response.data.errors ?? {}
      errors.value = Object.fromEntries(Object.entries(fieldErrors).map(([key, msgs]) => [key, msgs[0]]))
    } else {
      message.value = 'Tidak dapat terhubung ke server. Coba lagi beberapa saat.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="flex min-h-screen items-center justify-center p-4">
    <form class="flex w-full max-w-[420px] flex-col gap-6 rounded-4 bg-white p-8 shadow-e2" novalidate @submit.prevent="submit">
      <div class="flex flex-col items-center gap-4 text-center">
        <BrandLogo />
        <div class="flex flex-col gap-1">
          <h1 class="text-h6 font-bold text-neutral-1000">Masuk</h1>
          <p class="text-b3 text-neutral-800">Masuk untuk mengelola jadwal pribadimu.</p>
        </div>
      </div>

      <Alert v-if="message" status="error" :description="message">
        <template #icon><DangerCircleIcon /></template>
      </Alert>

      <Input
        v-model="email"
        label="Email"
        type="email"
        autocomplete="username"
        placeholder="nama@email.com"
        :status="errors.email ? 'error' : 'default'"
        :helper-text="errors.email"
      >
        <template #left-icon><LetterIcon /></template>
      </Input>

      <Input
        v-model="password"
        label="Password"
        type="password"
        autocomplete="current-password"
        placeholder="Masukkan password"
        :status="errors.password ? 'error' : 'default'"
        :helper-text="errors.password"
      >
        <template #left-icon><LockPasswordIcon /></template>
      </Input>

      <Button type="submit" size="lg" class="w-full" :disabled="loading">
        {{ loading ? 'Memproses...' : 'Masuk' }}
      </Button>
    </form>
  </main>
</template>
