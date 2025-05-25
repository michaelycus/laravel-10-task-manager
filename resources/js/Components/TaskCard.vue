<script setup>
defineProps({
  task: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <div class="bg-white shadow rounded-md p-3 mb-3 cursor-grab active:cursor-grabbing">
    <h4 class="font-semibold text-sm">{{ task.name }}</h4>
    <p v-if="task.description" class="text-xs text-gray-600 mt-1 truncate">
      {{ task.description }}
    </p>
    <div class="mt-2 text-xs text-gray-500">
      <span
        :class="{
          'bg-red-100 text-red-700': task.priority === 'high',
          'bg-yellow-100 text-yellow-700': task.priority === 'medium',
          'bg-green-100 text-green-700': task.priority === 'low',
        }"
        class="px-2 py-0.5 rounded-full text-xs"
      >
        Priority: {{ task.priority }}
      </span>
      <span v-if="task.due_date" class="ml-2"> Due: {{ task.due_date }} </span>
    </div>
  </div>
</template>
