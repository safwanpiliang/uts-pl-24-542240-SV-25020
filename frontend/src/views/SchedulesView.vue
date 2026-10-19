<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../lib/api'
import { auth, clearSession } from '../lib/auth'
import BrandLogo from '../components/BrandLogo.vue'
import Alert from '../components/ui/Alert.vue'
import Button from '../components/ui/Button.vue'
import Chip from '../components/ui/Chip.vue'
import Input from '../components/ui/Input.vue'
import Modal from '../components/ui/Modal.vue'
import Sidebar from '../components/ui/Sidebar.vue'
import StatCard from '../components/ui/StatCard.vue'
import Table from '../components/ui/Table.vue'
import { AddCircleIcon } from '@solar-icons/vue/bold-duotone/add-circle'
import { CheckCircleIcon } from '@solar-icons/vue/bold-duotone/check-circle'
import { ClockCircleIcon } from '@solar-icons/vue/bold-duotone/clock-circle'
import { CloseCircleIcon } from '@solar-icons/vue/bold-duotone/close-circle'
import { DangerCircleIcon } from '@solar-icons/vue/bold-duotone/danger-circle'
import { ListCheckIcon } from '@solar-icons/vue/bold-duotone/list-check'
import { Logout3Icon } from '@solar-icons/vue/bold-duotone/logout-3'
import { MagnifierIcon } from '@solar-icons/vue/bold-duotone/magnifier'
import { MapPointIcon } from '@solar-icons/vue/bold-duotone/map-point'
import { PenIcon } from '@solar-icons/vue/bold-duotone/pen'
import { TrashBinMinimalisticIcon } from '@solar-icons/vue/bold-duotone/trash-bin-minimalistic'

const router = useRouter()

const STATUS = {
  planned: { label: 'Terjadwal', chip: 'primary' },
  done: { label: 'Selesai', chip: 'success' },
  cancelled: { label: 'Dibatalkan', chip: 'error' },
}

const schedules = ref([])
const loading = ref(true)
const loadError = ref('')
const filter = ref('all')
const search = ref('')

const filters = computed(() => [
  { key: 'all', label: 'Semua Jadwal', icon: ListCheckIcon, active: filter.value === 'all' },
  { key: 'planned', label: 'Terjadwal', icon: ClockCircleIcon, active: filter.value === 'planned' },
  { key: 'done', label: 'Selesai', icon: CheckCircleIcon, active: filter.value === 'done' },
  { key: 'cancelled', label: 'Dibatalkan', icon: CloseCircleIcon, active: filter.value === 'cancelled' },
])

const counts = computed(() => ({
  all: schedules.value.length,
  planned: schedules.value.filter((s) => s.status === 'planned').length,
  done: schedules.value.filter((s) => s.status === 'done').length,
  cancelled: schedules.value.filter((s) => s.status === 'cancelled').length,
}))

const visible = computed(() => {
  const q = search.value.trim().toLowerCase()
  return schedules.value.filter((s) => {
    if (filter.value !== 'all' && s.status !== filter.value) return false
    if (!q) return true
    return s.title.toLowerCase().includes(q) || (s.location ?? '').toLowerCase().includes(q)
  })
})

const columns = [
  { key: 'title', header: 'Kegiatan' },
  { key: 'when', header: 'Waktu' },
  { key: 'location', header: 'Lokasi' },
  { key: 'status', header: 'Status', align: 'center', width: '130px' },
  { key: 'actions', header: 'Aksi', align: 'center', width: '110px' },
]

// ---- format tanggal ----
const dateFmt = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
const timeFmt = new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false })

function formatTime(date) {
  return timeFmt.format(date).replace('.', ':')
}

function formatRange(startIso, endIso) {
  const start = new Date(startIso)
  const end = new Date(endIso)
  const sameDay = dateFmt.format(start) === dateFmt.format(end)
  return {
    date: dateFmt.format(start),
    time: sameDay
      ? `${formatTime(start)} – ${formatTime(end)}`
      : `${formatTime(start)} – ${dateFmt.format(end)}, ${formatTime(end)}`,
  }
}

function toLocalInput(iso) {
  const d = new Date(iso)
  const pad = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
}

// ---- data ----
async function load() {
  loading.value = true
  loadError.value = ''
  try {
    const { data } = await api.get('/schedules')
    schedules.value = data.data
  } catch (error) {
    if (error.response?.status !== 401) {
      loadError.value = 'Gagal memuat jadwal. Periksa koneksi ke server lalu coba lagi.'
    }
  } finally {
    loading.value = false
  }
}

onMounted(load)

async function logout() {
  try {
    await api.post('/logout')
  } catch {
    // token sudah tidak berlaku di server, tetap lanjut keluar
  }
  clearSession()
  router.push({ name: 'login' })
}

// ---- form tambah/ubah ----
const formOpen = ref(false)
const editingId = ref(null)
const saving = ref(false)
const errors = ref({})
const formMessage = ref('')
const form = reactive({ title: '', description: '', location: '', start_at: '', end_at: '', status: 'planned' })

function openCreate() {
  const start = new Date()
  start.setMinutes(0, 0, 0)
  start.setHours(start.getHours() + 1)
  const end = new Date(start.getTime() + 60 * 60 * 1000)
  editingId.value = null
  Object.assign(form, {
    title: '',
    description: '',
    location: '',
    start_at: toLocalInput(start),
    end_at: toLocalInput(end),
    status: 'planned',
  })
  errors.value = {}
  formMessage.value = ''
  formOpen.value = true
}

function openEdit(schedule) {
  editingId.value = schedule.id
  Object.assign(form, {
    title: schedule.title,
    description: schedule.description ?? '',
    location: schedule.location ?? '',
    start_at: toLocalInput(schedule.start_at),
    end_at: toLocalInput(schedule.end_at),
    status: schedule.status,
  })
  errors.value = {}
  formMessage.value = ''
  formOpen.value = true
}

function validate() {
  const found = {}
  if (!form.title.trim()) found.title = 'Judul wajib diisi.'
  if (!form.start_at) found.start_at = 'Waktu mulai wajib diisi.'
  if (!form.end_at) found.end_at = 'Waktu selesai wajib diisi.'
  if (form.start_at && form.end_at && new Date(form.end_at) <= new Date(form.start_at)) {
    found.end_at = 'Waktu selesai harus setelah waktu mulai.'
  }
  return found
}

async function save() {
  errors.value = validate()
  formMessage.value = ''
  if (Object.keys(errors.value).length) return

  const payload = {
    title: form.title.trim(),
    description: form.description.trim() || null,
    location: form.location.trim() || null,
    start_at: new Date(form.start_at).toISOString(),
    end_at: new Date(form.end_at).toISOString(),
    status: form.status,
  }

  saving.value = true
  try {
    if (editingId.value) {
      await api.put(`/schedules/${editingId.value}`, payload)
    } else {
      await api.post('/schedules', payload)
    }
    formOpen.value = false
    await load()
  } catch (error) {
    if (error.response?.status === 422) {
      const fieldErrors = error.response.data.errors ?? {}
      errors.value = Object.fromEntries(Object.entries(fieldErrors).map(([key, msgs]) => [key, msgs[0]]))
    } else if (error.response?.status !== 401) {
      formMessage.value = 'Jadwal gagal disimpan. Coba lagi.'
    }
  } finally {
    saving.value = false
  }
}

// ---- hapus ----
const deleteTarget = ref(null)
const deleting = ref(false)
const deleteMessage = ref('')

function askDelete(schedule) {
  deleteTarget.value = schedule
  deleteMessage.value = ''
}

async function confirmDelete() {
  deleting.value = true
  deleteMessage.value = ''
  try {
    await api.delete(`/schedules/${deleteTarget.value.id}`)
    deleteTarget.value = null
    await load()
  } catch (error) {
    if (error.response?.status !== 401) deleteMessage.value = 'Jadwal gagal dihapus. Coba lagi.'
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <div class="flex min-h-screen">
    <div class="hidden lg:flex">
      <Sidebar :items="filters" :user="auth.user" @select="filter = $event" @logout="logout">
        <template #logo><BrandLogo /></template>
      </Sidebar>
    </div>

    <main class="flex min-w-0 flex-1 flex-col gap-6 p-4 lg:p-8">
      <header class="flex items-center justify-between gap-4 lg:hidden">
        <BrandLogo />
        <Button theme="secondary" variant="ghost" size="sm" aria-label="Keluar" @click="logout">
          <template #icon-left><Logout3Icon /></template>
        </Button>
      </header>

      <div class="flex flex-col gap-1">
        <h1 class="text-h6 font-bold text-neutral-1000">Jadwal Saya</h1>
        <p class="text-b3 text-neutral-800">
          Halo, {{ auth.user?.name }}. Kelola semua kegiatanmu di satu tempat.
        </p>
      </div>

      <div class="flex flex-wrap gap-2 lg:hidden">
        <button v-for="f in filters" :key="f.key" type="button" class="cursor-pointer" @click="filter = f.key">
          <Chip :variant="f.active ? 'solid' : 'tint'" status="primary">{{ f.label }}</Chip>
        </button>
      </div>

      <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        <StatCard :value="counts.all" label="Total jadwal">
          <template #icon><ListCheckIcon /></template>
        </StatCard>
        <StatCard variant="color" status="primary" :value="counts.planned" label="Terjadwal">
          <template #icon><ClockCircleIcon /></template>
        </StatCard>
        <StatCard variant="color" status="success" :value="counts.done" label="Selesai">
          <template #icon><CheckCircleIcon /></template>
        </StatCard>
        <StatCard variant="color" status="error" :value="counts.cancelled" label="Dibatalkan">
          <template #icon><CloseCircleIcon /></template>
        </StatCard>
      </div>

      <Alert v-if="loadError" status="error" :description="loadError">
        <template #icon><DangerCircleIcon /></template>
        <template #actions>
          <Button theme="error" variant="link" size="sm" @click="load">Coba lagi</Button>
        </template>
      </Alert>

      <Table
        :columns="columns"
        :data="visible"
        :empty-message="loading ? 'Memuat jadwal...' : 'Belum ada jadwal. Klik “Tambah Jadwal” untuk membuat yang pertama.'"
      >
        <template #toolbar>
          <div class="w-full sm:w-72">
            <Input v-model="search" size="md" variant="filled" placeholder="Cari judul atau lokasi">
              <template #left-icon><MagnifierIcon /></template>
            </Input>
          </div>
          <Button size="sm" @click="openCreate">
            <template #icon-left><AddCircleIcon /></template>
            Tambah Jadwal
          </Button>
        </template>

        <template #cell-title="{ row }">
          <div class="min-w-0">
            <p class="text-b2 truncate font-bold text-neutral-1000">{{ row.title }}</p>
            <p v-if="row.description" class="text-b4 line-clamp-1 text-neutral-800">{{ row.description }}</p>
          </div>
        </template>

        <template #cell-when="{ row }">
          <div>
            <p class="text-b3 text-neutral-1000">{{ formatRange(row.start_at, row.end_at).date }}</p>
            <p class="text-b4 text-neutral-800">{{ formatRange(row.start_at, row.end_at).time }}</p>
          </div>
        </template>

        <template #cell-location="{ row }">
          <p v-if="row.location" class="text-b3 flex min-w-0 items-center gap-1 text-neutral-900">
            <span class="size-4 shrink-0 text-neutral-700 [&>svg]:size-full"><MapPointIcon /></span>
            <span class="truncate">{{ row.location }}</span>
          </p>
          <p v-else class="text-b3 text-neutral-700">-</p>
        </template>

        <template #cell-status="{ row }">
          <Chip variant="tint" :status="STATUS[row.status].chip" size="sm">{{ STATUS[row.status].label }}</Chip>
        </template>

        <template #cell-actions="{ row }">
          <div class="flex items-center gap-1">
            <Button variant="ghost" size="sm" aria-label="Ubah jadwal" @click="openEdit(row)">
              <template #icon-left><PenIcon /></template>
            </Button>
            <Button theme="error" variant="ghost" size="sm" aria-label="Hapus jadwal" @click="askDelete(row)">
              <template #icon-left><TrashBinMinimalisticIcon /></template>
            </Button>
          </div>
        </template>
      </Table>
    </main>
  </div>

  <Modal
    :open="formOpen"
    :title="editingId ? 'Ubah Jadwal' : 'Tambah Jadwal'"
    description="Isi detail kegiatan yang akan dijadwalkan."
    panel-class="w-[480px]"
    @close="formOpen = false"
  >
    <form id="schedule-form" class="flex flex-col gap-4" novalidate @submit.prevent="save">
      <Alert v-if="formMessage" status="error" :description="formMessage">
        <template #icon><DangerCircleIcon /></template>
      </Alert>

      <Input v-model="form.title" label="Judul" placeholder="Contoh: Rapat kelompok"
        :status="errors.title ? 'error' : 'default'" :helper-text="errors.title" />
      <Input v-model="form.description" label="Deskripsi" multiline rows="3" placeholder="Catatan singkat (opsional)"
        :status="errors.description ? 'error' : 'default'" :helper-text="errors.description" />
      <Input v-model="form.location" label="Lokasi" placeholder="Contoh: Ruang 301 (opsional)"
        :status="errors.location ? 'error' : 'default'" :helper-text="errors.location" />

      <div class="grid grid-cols-1 gap-4">
        <Input v-model="form.start_at" label="Mulai" type="datetime-local"
          :status="errors.start_at ? 'error' : 'default'" :helper-text="errors.start_at" />
        <Input v-model="form.end_at" label="Selesai" type="datetime-local"
          :status="errors.end_at ? 'error' : 'default'" :helper-text="errors.end_at" />
      </div>

      <div class="flex flex-col gap-2">
        <span class="text-b2 text-neutral-1000">Status</span>
        <div class="flex flex-wrap gap-2">
          <button v-for="(meta, key) in STATUS" :key="key" type="button" class="cursor-pointer" @click="form.status = key">
            <Chip :variant="form.status === key ? 'solid' : 'outline'" :status="meta.chip">{{ meta.label }}</Chip>
          </button>
        </div>
        <p v-if="errors.status" class="text-b3 text-red-200">{{ errors.status }}</p>
      </div>
    </form>

    <template #actions>
      <Button theme="neutral" variant="outline" :disabled="saving" @click="formOpen = false">Batal</Button>
      <Button type="submit" form="schedule-form" :disabled="saving">{{ saving ? 'Menyimpan...' : 'Simpan' }}</Button>
    </template>
  </Modal>

  <Modal
    :open="deleteTarget !== null"
    title="Hapus jadwal?"
    :description="deleteTarget ? `“${deleteTarget.title}” akan dihapus permanen.` : ''"
    @close="deleteTarget = null"
  >
    <Alert v-if="deleteMessage" status="error" :description="deleteMessage">
      <template #icon><DangerCircleIcon /></template>
    </Alert>
    <template #actions>
      <Button theme="neutral" variant="outline" :disabled="deleting" @click="deleteTarget = null">Batal</Button>
      <Button theme="error" :disabled="deleting" @click="confirmDelete">{{ deleting ? 'Menghapus...' : 'Hapus' }}</Button>
    </template>
  </Modal>
</template>
