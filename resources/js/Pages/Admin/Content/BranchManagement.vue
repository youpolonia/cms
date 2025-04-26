<template>
  <AdminLayout>
    <h1 class="text-2xl font-bold mb-6">Content Branch Management</h1>
    
    <div class="space-y-8">
      <!-- Branch Creation Form -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Create New Branch</h2>
        <form @submit.prevent="createBranch">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <TextInput 
              v-model="newBranch.name"
              label="Branch Name"
              required
            />
            <SelectInput
              v-model="newBranch.baseVersion"
              label="Base Version"
              :options="versions"
              required
            />
          </div>
          <PrimaryButton type="submit">Create Branch</PrimaryButton>
        </form>
      </div>

      <!-- Branches Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b">
          <h2 class="text-xl font-semibold">Existing Branches</h2>
        </div>
        <Table>
          <template #headers>
            <TableHeader>Name</TableHeader>
            <TableHeader>Base Version</TableHeader>  
            <TableHeader>Status</TableHeader>
            <TableHeader>Actions</TableHeader>
          </template>
          <template #rows v-if="branches.length">
            <TableRow v-for="branch in branches" :key="branch.id">
              <TableCell>{{ branch.name }}</TableCell>
              <TableCell>{{ branch.base_version }}</TableCell>
              <TableCell>{{ branch.status }}</TableCell>
              <TableCell class="space-x-2">
                <SecondaryButton @click="mergeDialogOpen = true">Merge</SecondaryButton>
                <DangerButton>Delete</DangerButton>
              </TableCell>
            </TableRow>
          </template>
          <template #empty v-else>
            <TableCell colspan="4" class="text-center py-12 text-gray-500">
              No branches found
            </TableCell>
          </template>
        </Table>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import TextInput from '@/Components/Form/TextInput.vue'
import SelectInput from '@/Components/Form/SelectInput.vue'
import PrimaryButton from '@/Components/Button/Primary.vue'
import SecondaryButton from '@/Components/Button/Secondary.vue'
import DangerButton from '@/Components/Button/Danger.vue'
import Table from '@/Components/Table.vue'
import TableHeader from '@/Components/Table/Header.vue'
import TableRow from '@/Components/Table/Row.vue'
import TableCell from '@/Components/Table/Cell.vue'
import { ref, onMounted } from 'vue'

const branches = ref([])
const newBranch = ref({
  name: '',
  baseVersion: null
})
const versions = ref([])
const mergeDialogOpen = ref(false)

const fetchBranches = async () => {
  // To be implemented
}

const createBranch = async () => {
  // To be implemented
}

onMounted(() => {
  fetchBranches()
})
</script>