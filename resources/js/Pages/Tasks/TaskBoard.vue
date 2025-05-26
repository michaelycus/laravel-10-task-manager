<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import draggable from 'vuedraggable';
import { useTaskStore } from '@/Stores/taskStore';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TaskCard from '@/Components/TaskCard.vue';

const props = defineProps({
  initialTasks: {
    type: Array,
    default: () => [],
  },
});

const taskStore = useTaskStore();

onMounted(async () => {
  if (props.initialTasks && props.initialTasks.length > 0) {
    taskStore.setTasks(props.initialTasks);
  } else {
    await taskStore.fetchTasks(); // Ensure tasks are fetched before listening
  }
  taskStore.listenForTaskEvents(); // Start listening when component mounts
});

onUnmounted(() => {
  taskStore.stopListeningForTaskEvents(); // Stop listening when component unmounts
});

const handleTaskMoved = (event, targetLaneId) => {
  // `event` can be `added`, `removed`, `moved`
  // We are interested in `added` to a new lane
  if (event.added) {
    const taskId = event.added.element.id;
    taskStore.updateTaskStatus(taskId, targetLaneId);
  }
  // Note: vue.draggable.next v-model handles local array updates automatically.
  // The `updateTaskStatus` handles the API call and Pinia state persistence.
};

// This is needed for v-model with vue.draggable.next when using a computed property
// It creates writable computed properties for each lane's tasks
const laneTasks = computed(() => {
  const result = {};
  taskStore.lanes.forEach(lane => {
    result[lane.id] = computed({
      get: () => taskStore.tasksByLane[lane.id] || [],
      set: newTasks => {
        // This setter might be called by vue.draggable.next
        // We need to ensure Pinia state is the source of truth for cross-lane moves
        // For same-lane reordering, this is fine.
        // For cross-lane, the `handleTaskMoved` with `updateTaskStatus` is more critical.
        // It's complex to directly update a filtered computed property's source.
        // The main logic for status change should be in `handleTaskMoved`.
        console.log(
          `Tasks for lane ${lane.id} set (likely due to internal reorder):`,
          newTasks.map(t => t.id)
        );
      },
    });
  });
  return result;
});
</script>

<template>
  <Head title="Task Board" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Task Board</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div v-if="taskStore.isLoading" class="text-center text-gray-500">Loading tasks...</div>
        <div v-if="taskStore.error" class="text-center text-red-500 p-4 bg-red-100 rounded-md">
          {{ taskStore.error }}
        </div>

        <div v-if="!taskStore.isLoading && !taskStore.error" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div v-for="lane in taskStore.lanes" :key="lane.id" class="bg-gray-200 p-4 rounded-lg shadow">
            <h3 class="font-semibold text-lg mb-4 text-gray-700">
              {{ lane.title }} ({{ laneTasks[lane.id].value.length }})
            </h3>
            <draggable
              :list="laneTasks[lane.id].value"
              item-key="id"
              group="tasks"
              class="min-h-[200px] space-y-3"
              ghost-class="ghost-card"
              @change="event => handleTaskMoved(event, lane.id)"
            >
              <template #item="{ element: task }">
                <TaskCard :task="task" />
              </template>
            </draggable>
            <div v-if="!laneTasks[lane.id].value.length" class="text-sm text-gray-400 text-center py-10">
              No tasks here.
            </div>
          </div>
        </div>
        <div class="mt-8 text-center">
          <Link :href="route('tasks-list.index')" class="text-indigo-600 hover:text-indigo-800">
            Go to Task List View
          </Link>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.ghost-card {
  @apply opacity-50 bg-blue-100 rounded-md;
}
.min-h-\[200px\] {
  /* Ensure min-h-[200px] is processed by Tailwind */
  min-height: 200px;
}
</style>
