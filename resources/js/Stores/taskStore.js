import { defineStore } from 'pinia';
import axios from 'axios';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3'; // To get initial props if needed

// Simple debounce function
function debounce(fn, delay) {
  let timeoutId;
  return function (...args) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => fn.apply(this, args), delay);
  };
}

export const useTaskStore = defineStore('tasks', () => {
  const tasks = ref([]);
  const isLoading = ref(false);
  const error = ref(null);

  // Define lanes based on completion status
  const lanes = ref([
    { id: 'pending', title: 'Pending', statusKey: null }, // Represents completed_at = null
    { id: 'completed', title: 'Completed', statusKey: true }, // Represents completed_at != null
  ]);

  const tasksByLane = computed(() => {
    const grouped = {};
    lanes.value.forEach(lane => {
      grouped[lane.id] = tasks.value.filter(task => {
        return lane.id === 'pending' ? !task.is_completed : task.is_completed;
      });
    });
    return grouped;
  });

  async function fetchTasks(filters = {}) {
    isLoading.value = true;
    error.value = null;
    try {
      // Construct query parameters for API
      const queryParams = new URLSearchParams();
      if (filters.name) queryParams.append('name', filters.name);
      if (filters.priority) queryParams.append('priority', filters.priority);
      // if (filters.status) queryParams.append('status', filters.status); // For general status filter
      if (filters.sort_by) queryParams.append('sort_by', filters.sort_by);
      if (filters.sort_direction) queryParams.append('sort_direction', filters.sort_direction);
      queryParams.append('per_page', 50); // Fetch more for a board view

      const response = await axios.get(`/api/v1/tasks?${queryParams.toString()}`);
      tasks.value = response.data.data; // Assuming TaskCollection wraps in 'data'
    } catch (e) {
      error.value = 'Failed to fetch tasks.';
      console.error(e);
    } finally {
      isLoading.value = false;
    }
  }

  // Debounced version of the actual API update
  const debouncedApiUpdate = debounce(async (taskId, updates, originalTaskState) => {
    try {
      const response = await axios.patch(`/api/v1/tasks/${taskId}`, updates);
      // Update the task in the store with the full response from the server
      const index = tasks.value.findIndex(t => t.id === taskId);
      if (index !== -1) {
        tasks.value[index] = {
          ...tasks.value[index],
          ...response.data.data,
        };
      }
    } catch (e) {
      error.value = `Failed to update task ${taskId}. Reverting.`;
      console.error(e);
      // Revert optimistic update
      const index = tasks.value.findIndex(t => t.id === taskId);
      if (index !== -1 && originalTaskState) {
        tasks.value[index] = originalTaskState;
      }
    }
  }, 750); // 750ms debounce

  async function updateTaskStatus(taskId, newLaneId) {
    const taskIndex = tasks.value.findIndex(t => t.id === taskId);
    if (taskIndex === -1) return;

    const originalTaskState = JSON.parse(JSON.stringify(tasks.value[taskIndex])); // Deep copy for revert

    // Optimistic UI Update
    const task = tasks.value[taskIndex];
    const newIsCompleted = newLaneId === 'completed';
    task.is_completed = newIsCompleted;
    task.completed_at = newIsCompleted ? new Date().toISOString() : null;

    // Prepare data for API call
    const updates = {
      mark_as_completed: newIsCompleted,
    };

    // Call the debounced API update function
    debouncedApiUpdate(taskId, updates, originalTaskState);
  }

  // Function to set tasks if passed from Inertia props
  function setTasks(initialTasks) {
    if (initialTasks && initialTasks.length > 0) {
      tasks.value = initialTasks;
    }
  }

  // --- Real-time Event Handling ---
  function handleTaskCreated(eventData) {
    console.log('Event: task.created', eventData);
    const newTask = eventData.task;
    // Avoid duplicates if the current user created it (API might have already added it)
    if (!tasks.value.find(t => t.id === newTask.id)) {
      tasks.value.unshift(newTask); // Add to the beginning or sort as needed
    }
  }

  function handleTaskUpdated(eventData) {
    console.log('Event: task.updated', eventData);
    const updatedTask = eventData.task;
    const index = tasks.value.findIndex(t => t.id === updatedTask.id);
    if (index !== -1) {
      tasks.value[index] = { ...tasks.value[index], ...updatedTask };
    } else {
      // If task wasn't in the list, maybe add it (depends on app logic)
      tasks.value.unshift(updatedTask);
    }
  }

  function handleTaskDeleted(eventData) {
    console.log('Event: task.deleted', eventData);
    const { taskId } = eventData;
    tasks.value = tasks.value.filter(t => t.id !== taskId);
  }

  function listenForTaskEvents() {
    if (window.Echo) {
      window.Echo.channel('tasks') // Public channel named 'tasks'
        .listen('.task.created', handleTaskCreated) // Note the dot prefix for broadcastAs
        .listen('.task.updated', handleTaskUpdated)
        .listen('.task.deleted', handleTaskDeleted);
      console.log("Subscribed to 'tasks' channel for real-time updates.");
    } else {
      console.warn('Laravel Echo not available. Real-time updates disabled.');
    }
  }

  function stopListeningForTaskEvents() {
    if (window.Echo) {
      window.Echo.leaveChannel('tasks');
      console.log("Left 'tasks' channel.");
    }
  }

  return {
    tasks,
    isLoading,
    error,
    lanes,
    tasksByLane,
    fetchTasks,
    updateTaskStatus,
    setTasks,
    // Expose listener functions to be called from components
    listenForTaskEvents,
    stopListeningForTaskEvents,
  };
});
