<template>
  <div>
    <h1>Edit File</h1>
    <form @submit.prevent="submit">
      <div>
        <label for="name">Name</label>
        <input type="text" v-model="form.name" id="name" />
      </div>
      <button type="submit">Update</button>
    </form>
  </div>
</template>

<script>
import { useForm } from '@inertiajs/inertia-vue3';

export default {
  props: {
    file: Object,
  },
  setup(props) {
    const form = useForm({
      name: props.file.name,
    });

    const submit = () => {
      form.put(`/files/${props.file.id}`);
    };

    return { form, submit };
  },
};
</script>
