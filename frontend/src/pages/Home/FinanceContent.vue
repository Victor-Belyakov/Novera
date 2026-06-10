<template>
  <div class="w-full min-w-0 flex flex-col gap-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div class="flex gap-2 rounded-lg bg-gray-100 p-1">
        <button
          type="button"
          class="px-4 py-2 rounded-md text-sm font-medium transition cursor-pointer"
          :class="activeTab === 'operations' ? 'bg-white text-blue-950 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
          @click="activeTab = 'operations'"
        >
          Операции
        </button>
        <button
          type="button"
          class="px-4 py-2 rounded-md text-sm font-medium transition cursor-pointer"
          :class="activeTab === 'plans' ? 'bg-white text-blue-950 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
          @click="activeTab = 'plans'"
        >
          План
        </button>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <button
          v-if="activeTab === 'operations'"
          type="button"
          @click="openFinanceModal"
          class="px-4 py-2 app-btn-primary rounded-md transition-colors text-sm font-medium cursor-pointer flex items-center gap-2"
        >
          <span class="text-lg leading-none">+</span>
          Добавить операцию
        </button>
        <button
          v-else
          type="button"
          @click="openPlanModal"
          class="px-4 py-2 app-btn-primary rounded-md transition-colors text-sm font-medium cursor-pointer flex items-center gap-2"
        >
          <span class="text-lg leading-none">+</span>
          Добавить план
        </button>
        <button
          type="button"
          @click="openCategoryModal"
          class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors text-sm font-medium cursor-pointer"
        >
          Категория
        </button>
      </div>
    </div>

    <div v-if="activeTab === 'operations'" class="flex flex-wrap items-center justify-between gap-3">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3 flex-1">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
          <div class="text-sm font-medium text-gray-500">Доходы</div>
          <div class="mt-2 text-2xl font-bold text-green-600">{{ formatCurrency(totalIncome) }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
          <div class="text-sm font-medium text-gray-500">Расходы</div>
          <div class="mt-2 text-2xl font-bold text-red-600">{{ formatCurrency(totalExpense) }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
          <div class="text-sm font-medium text-gray-500">Баланс</div>
          <div class="mt-2 text-2xl font-bold" :class="balance >= 0 ? 'text-blue-950' : 'text-red-600'">
            {{ formatCurrency(balance) }}
          </div>
        </div>
      </div>

      <div class="w-full sm:w-56">
        <AppSelect v-model="typeFilter" :options="typeFilterOptions" placeholder="Все типы" />
      </div>
    </div>

    <div v-else class="flex flex-wrap items-end justify-between gap-3">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3 flex-1">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
          <div class="text-sm font-medium text-gray-500">План доходов</div>
          <div class="mt-2 text-2xl font-bold text-green-600">{{ formatCurrency(planSummary.planned_income) }}</div>
          <div class="mt-1 text-xs text-gray-400">Факт: {{ formatCurrency(planSummary.actual_income) }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
          <div class="text-sm font-medium text-gray-500">План расходов</div>
          <div class="mt-2 text-2xl font-bold text-red-600">{{ formatCurrency(planSummary.planned_expense) }}</div>
          <div class="mt-1 text-xs text-gray-400">Факт: {{ formatCurrency(planSummary.actual_expense) }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
          <div class="text-sm font-medium text-gray-500">Баланс месяца</div>
          <div class="mt-2 text-2xl font-bold text-blue-950">{{ formatCurrency(planSummary.balance_plan) }}</div>
          <div class="mt-1 text-xs text-gray-400">Факт: {{ formatCurrency(planSummary.balance_actual) }}</div>
        </div>
      </div>

      <div class="w-full sm:w-48">
        <label class="app-label">Месяц</label>
        <input v-model="selectedMonth" type="month" class="app-input" />
      </div>
    </div>

    <div v-if="activeTab === 'operations'" class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full divide-y divide-gray-200 border-collapse">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Тип</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Категория</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Операция</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Сумма</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loadingOperations">
              <td colspan="5" class="px-4 py-8 text-center"><AppSpinner block /></td>
            </tr>
            <tr v-else-if="errorOperations">
              <td colspan="5" class="px-4 py-8 text-left text-red-600">{{ errorOperations }}</td>
            </tr>
            <tr v-else-if="!filteredFinances.length">
              <td colspan="5" class="px-4 py-8 text-left text-gray-500">Нет финансовых операций</td>
            </tr>
            <tr v-else v-for="finance in filteredFinances" :key="finance.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatDate(finance.operation_date) }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm">
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" :class="finance.type === 'income' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                  {{ finance.type_label }}
                </span>
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ finance.category_title || 'Без категории' }}</td>
              <td class="px-4 py-3 text-sm text-gray-900">
                <div class="font-medium">{{ finance.title }}</div>
                <div v-if="finance.description" class="text-xs text-gray-500 mt-1">{{ finance.description }}</div>
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold" :class="finance.type === 'income' ? 'text-green-600' : 'text-red-600'">
                {{ finance.type === 'income' ? '+' : '-' }}{{ formatCurrency(finance.amount) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-else class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full divide-y divide-gray-200 border-collapse">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Тип</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Категория</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">План</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">План</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Факт</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Отклонение</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loadingPlans">
              <td colspan="6" class="px-4 py-8 text-center"><AppSpinner block /></td>
            </tr>
            <tr v-else-if="errorPlans">
              <td colspan="6" class="px-4 py-8 text-left text-red-600">{{ errorPlans }}</td>
            </tr>
            <tr v-else-if="!planSummary.rows.length">
              <td colspan="6" class="px-4 py-8 text-left text-gray-500">На этот месяц планы не заведены</td>
            </tr>
            <tr v-else v-for="row in planSummary.rows" :key="`${row.type}-${row.category_id ?? 'none'}-${row.title}`" class="hover:bg-gray-50">
              <td class="px-4 py-3 whitespace-nowrap text-sm">
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" :class="row.type === 'income' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                  {{ row.type_label }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-900">
                <div class="font-medium">{{ row.category_title || 'Без категории' }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ row.title }}</div>
              </td>
              <td class="px-4 py-3 text-sm text-gray-900">{{ row.title }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold text-gray-900">{{ formatCurrency(row.planned_amount) }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold text-gray-900">{{ formatCurrency(row.actual_amount) }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold" :class="differenceClass(row)">{{ formatSignedCurrency(row.difference) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="showFinanceModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="closeFinanceModal">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full overflow-hidden border border-gray-100 flex flex-col">
          <div class="p-6 flex items-center justify-between rounded-t-xl shrink-0 app-modal-header">
            <h3 class="text-lg font-semibold">Новая операция</h3>
            <button type="button" @click="closeFinanceModal" class="p-2 rounded-lg hover:bg-white/80 text-blue-700 transition cursor-pointer" aria-label="Закрыть">✕</button>
          </div>
          <form @submit.prevent="submitFinance" class="p-6 space-y-5">
            <div>
              <label class="app-label app-label--required">Название</label>
              <input v-model="financeForm.title" type="text" required class="app-input" placeholder="Например, Зарплата или Продукты" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="app-label app-label--required">Тип</label>
                <AppSelect v-model="financeForm.type" :options="typeOptions" placeholder="Выберите тип" />
              </div>
              <div>
                <label class="app-label app-label--required">Сумма</label>
                <input v-model="financeForm.amount" type="number" min="0" step="0.01" required class="app-input" placeholder="0.00" />
              </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="app-label">Категория</label>
                <AppSelect v-model="financeForm.category_id" :options="categoryOptions" placeholder="Выберите категорию" />
              </div>
              <div>
                <label class="app-label">Дата операции</label>
                <AppDatePicker v-model="financeForm.operation_date" placeholder="Выберите дату" />
              </div>
            </div>
            <div>
              <label class="app-label">Комментарий</label>
              <textarea v-model="financeForm.description" rows="3" class="app-input" placeholder="Комментарий" />
            </div>
            <div class="flex gap-3 pt-3">
              <button type="submit" :disabled="savingFinance" class="px-5 py-2.5 app-btn-primary rounded-lg disabled:opacity-50 text-sm font-medium transition">{{ savingFinance ? 'Сохранение...' : 'Создать' }}</button>
              <button type="button" @click="closeFinanceModal" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium transition">Отмена</button>
            </div>
            <p v-if="financeFormError" class="text-sm text-red-600">{{ financeFormError }}</p>
          </form>
        </div>
      </div>
    </Teleport>

    <Teleport to="body">
      <div v-if="showPlanModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="closePlanModal">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full overflow-hidden border border-gray-100 flex flex-col">
          <div class="p-6 flex items-center justify-between rounded-t-xl shrink-0 app-modal-header">
            <h3 class="text-lg font-semibold">Новый план</h3>
            <button type="button" @click="closePlanModal" class="p-2 rounded-lg hover:bg-white/80 text-blue-700 transition cursor-pointer" aria-label="Закрыть">✕</button>
          </div>
          <form @submit.prevent="submitPlan" class="p-6 space-y-5">
            <div>
              <label class="app-label app-label--required">Название</label>
              <input v-model="planForm.title" type="text" required class="app-input" placeholder="Например, Продукты на месяц" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="app-label app-label--required">Тип</label>
                <AppSelect v-model="planForm.type" :options="typeOptions" placeholder="Выберите тип" />
              </div>
              <div>
                <label class="app-label app-label--required">Плановая сумма</label>
                <input v-model="planForm.planned_amount" type="number" min="0" step="0.01" required class="app-input" placeholder="0.00" />
              </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="app-label">Категория</label>
                <AppSelect v-model="planForm.category_id" :options="categoryOptions" placeholder="Выберите категорию" />
              </div>
              <div>
                <label class="app-label app-label--required">Месяц</label>
                <input v-model="planForm.month" type="month" class="app-input" required />
              </div>
            </div>
            <div class="flex gap-3 pt-3">
              <button type="submit" :disabled="savingPlan" class="px-5 py-2.5 app-btn-primary rounded-lg disabled:opacity-50 text-sm font-medium transition">{{ savingPlan ? 'Сохранение...' : 'Создать' }}</button>
              <button type="button" @click="closePlanModal" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium transition">Отмена</button>
            </div>
            <p v-if="planFormError" class="text-sm text-red-600">{{ planFormError }}</p>
          </form>
        </div>
      </div>
    </Teleport>

    <Teleport to="body">
      <div v-if="showCategoryModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="closeCategoryModal">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full overflow-hidden border border-gray-100 flex flex-col">
          <div class="p-6 flex items-center justify-between rounded-t-xl shrink-0 app-modal-header">
            <h3 class="text-lg font-semibold">Новая категория финансов</h3>
            <button type="button" @click="closeCategoryModal" class="p-2 rounded-lg hover:bg-white/80 text-blue-700 transition cursor-pointer" aria-label="Закрыть">✕</button>
          </div>
          <form @submit.prevent="submitCategory" class="p-6 space-y-5">
            <div>
              <label class="app-label app-label--required">Название</label>
              <input v-model="categoryForm.title" type="text" required class="app-input" placeholder="Например, Продукты" />
            </div>
            <div class="flex gap-3 pt-3">
              <button type="submit" :disabled="savingCategory" class="px-5 py-2.5 app-btn-primary rounded-lg disabled:opacity-50 text-sm font-medium transition">{{ savingCategory ? 'Сохранение...' : 'Создать' }}</button>
              <button type="button" @click="closeCategoryModal" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium transition">Отмена</button>
            </div>
            <p v-if="categoryFormError" class="text-sm text-red-600">{{ categoryFormError }}</p>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { apiGet, apiPost, handleResponse } from '@/api'
import { API_ENDPOINTS } from '@/constants/api'
import AppSpinner from '@/components/AppSpinner.vue'
import AppSelect from '@/components/AppSelect.vue'
import AppDatePicker from '@/components/AppDatePicker.vue'

const activeTab = ref('operations')
const selectedMonth = ref(new Date().toISOString().slice(0, 7))

const finances = ref([])
const categories = ref([])
const loadingOperations = ref(false)
const errorOperations = ref(null)
const typeFilter = ref('')

const planSummary = ref({
  planned_income: '0.00',
  actual_income: '0.00',
  planned_expense: '0.00',
  actual_expense: '0.00',
  balance_plan: '0.00',
  balance_actual: '0.00',
  rows: [],
})
const loadingPlans = ref(false)
const errorPlans = ref(null)

const showFinanceModal = ref(false)
const savingFinance = ref(false)
const financeFormError = ref(null)

const showPlanModal = ref(false)
const savingPlan = ref(false)
const planFormError = ref(null)

const showCategoryModal = ref(false)
const savingCategory = ref(false)
const categoryFormError = ref(null)

const financeForm = reactive({
  title: '',
  amount: '',
  type: 'expense',
  category_id: null,
  operation_date: '',
  description: '',
})

const planForm = reactive({
  title: '',
  type: 'expense',
  planned_amount: '',
  category_id: null,
  month: selectedMonth.value,
})

const categoryForm = reactive({
  title: '',
})

const typeOptions = [
  { value: 'expense', label: 'Расход' },
  { value: 'income', label: 'Доход' },
]

const typeFilterOptions = [{ value: '', label: 'Все типы' }, ...typeOptions]

const categoryOptions = computed(() => categories.value.map((category) => ({
  value: category.id,
  label: category.title,
})))

const filteredFinances = computed(() => {
  if (!typeFilter.value) return finances.value
  return finances.value.filter((finance) => finance.type === typeFilter.value)
})

const totalIncome = computed(() => filteredFinances.value
  .filter((finance) => finance.type === 'income')
  .reduce((sum, finance) => sum + Number(finance.amount || 0), 0))

const totalExpense = computed(() => filteredFinances.value
  .filter((finance) => finance.type === 'expense')
  .reduce((sum, finance) => sum + Number(finance.amount || 0), 0))

const balance = computed(() => totalIncome.value - totalExpense.value)

function formatDate(dateStr) {
  if (!dateStr) return '—'
  try {
    return new Date(dateStr).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' })
  } catch {
    return dateStr
  }
}

function formatCurrency(value) {
  const amount = Number(value || 0)
  return new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount)
}

function formatSignedCurrency(value) {
  const amount = Number(value || 0)
  const sign = amount > 0 ? '+' : ''
  return `${sign}${formatCurrency(amount)}`
}

function differenceClass(row) {
  const value = Number(row.difference || 0)
  if (row.type === 'expense') {
    return value >= 0 ? 'text-green-600' : 'text-red-600'
  }
  return value >= 0 ? 'text-green-600' : 'text-red-600'
}

async function fetchFinances() {
  loadingOperations.value = true
  errorOperations.value = null
  try {
    const response = await apiGet(API_ENDPOINTS.FINANCES.LIST)
    const data = await handleResponse(response)
    finances.value = Array.isArray(data) ? data : []
  } catch (e) {
    errorOperations.value = e.message || 'Ошибка загрузки финансов'
    finances.value = []
  } finally {
    loadingOperations.value = false
  }
}

async function fetchCategories() {
  try {
    const response = await apiGet(API_ENDPOINTS.FINANCE_CATEGORIES.LIST)
    const data = await handleResponse(response)
    categories.value = Array.isArray(data) ? data : []
  } catch {
    categories.value = []
  }
}

async function fetchPlanSummary() {
  loadingPlans.value = true
  errorPlans.value = null
  try {
    const response = await apiGet(`${API_ENDPOINTS.FINANCE_PLANS.SUMMARY}?month=${selectedMonth.value}`)
    const data = await handleResponse(response)
    planSummary.value = {
      planned_income: data?.planned_income ?? '0.00',
      actual_income: data?.actual_income ?? '0.00',
      planned_expense: data?.planned_expense ?? '0.00',
      actual_expense: data?.actual_expense ?? '0.00',
      balance_plan: data?.balance_plan ?? '0.00',
      balance_actual: data?.balance_actual ?? '0.00',
      rows: Array.isArray(data?.rows) ? data.rows : [],
    }
  } catch (e) {
    errorPlans.value = e.message || 'Ошибка загрузки планов'
    planSummary.value = {
      planned_income: '0.00',
      actual_income: '0.00',
      planned_expense: '0.00',
      actual_expense: '0.00',
      balance_plan: '0.00',
      balance_actual: '0.00',
      rows: [],
    }
  } finally {
    loadingPlans.value = false
  }
}

function resetFinanceForm() {
  financeForm.title = ''
  financeForm.amount = ''
  financeForm.type = 'expense'
  financeForm.category_id = null
  financeForm.operation_date = ''
  financeForm.description = ''
  financeFormError.value = null
}

function resetPlanForm() {
  planForm.title = ''
  planForm.type = 'expense'
  planForm.planned_amount = ''
  planForm.category_id = null
  planForm.month = selectedMonth.value
  planFormError.value = null
}

function openFinanceModal() {
  resetFinanceForm()
  showFinanceModal.value = true
}

function closeFinanceModal() {
  showFinanceModal.value = false
}

function openPlanModal() {
  resetPlanForm()
  showPlanModal.value = true
}

function closePlanModal() {
  showPlanModal.value = false
}

function openCategoryModal() {
  categoryForm.title = ''
  categoryFormError.value = null
  showCategoryModal.value = true
}

function closeCategoryModal() {
  showCategoryModal.value = false
}

async function submitFinance() {
  savingFinance.value = true
  financeFormError.value = null
  try {
    const payload = {
      title: financeForm.title.trim(),
      amount: financeForm.amount,
      type: financeForm.type,
      description: financeForm.description?.trim() || null,
      category_id: financeForm.category_id,
      operation_date: financeForm.operation_date || '',
    }

    const response = await apiPost(API_ENDPOINTS.FINANCES.CREATE, payload)
    await handleResponse(response)
    closeFinanceModal()
    await Promise.all([fetchFinances(), fetchPlanSummary()])
  } catch (e) {
    financeFormError.value = e.message || 'Ошибка при создании операции'
  } finally {
    savingFinance.value = false
  }
}

async function submitPlan() {
  savingPlan.value = true
  planFormError.value = null
  try {
    const payload = {
      title: planForm.title.trim(),
      type: planForm.type,
      planned_amount: planForm.planned_amount,
      category_id: planForm.category_id,
      month: planForm.month,
    }

    const response = await apiPost(API_ENDPOINTS.FINANCE_PLANS.CREATE, payload)
    await handleResponse(response)
    closePlanModal()
    await fetchPlanSummary()
  } catch (e) {
    planFormError.value = e.message || 'Ошибка при создании плана'
  } finally {
    savingPlan.value = false
  }
}

async function submitCategory() {
  savingCategory.value = true
  categoryFormError.value = null
  try {
    const response = await apiPost(API_ENDPOINTS.FINANCE_CATEGORIES.CREATE, {
      title: categoryForm.title.trim(),
    })
    const createdCategory = await handleResponse(response)
    await fetchCategories()
    financeForm.category_id = createdCategory?.id ?? financeForm.category_id
    planForm.category_id = createdCategory?.id ?? planForm.category_id
    closeCategoryModal()
  } catch (e) {
    categoryFormError.value = e.message || 'Ошибка при создании категории'
  } finally {
    savingCategory.value = false
  }
}

watch(selectedMonth, async (month) => {
  planForm.month = month
  await fetchPlanSummary()
})

onMounted(async () => {
  await Promise.all([fetchFinances(), fetchCategories(), fetchPlanSummary()])
})
</script>
