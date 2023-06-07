<template>
    <a-list
      :grid="{ gutter: 16, xs: 1, sm: 2, md: 4, lg: 4, xl: 6, xxl: 8, xxxl: 10 }"
      :data-source="data"
    >
      <template #renderItem="{ item }">
        <a-list-item>
          <a-card :title="item.title">Card content</a-card>
        </a-list-item>
      </template>
    </a-list>
  </template>
  <script>
  import { defineComponent } from 'vue';
  const data = [{
    title: 'Title 1',
  }, {
    title: 'Title 2',
  }, {
    title: 'Title 3',
  }, {
    title: 'Title 4',
  }, {
    title: 'Title 5',
  }, {
    title: 'Title 6',
  }, {
    title: 'Title 6',
  }, {
    title: 'Title 6',
  }, {
    title: 'Title 6',
  }, {
    title: 'Title 6',
  }, {
    title: 'Title 6',
  }, {
    title: 'Title 6',
  }, {
    title: 'Title 6',
  }, {
    title: 'Title 6',
  }, {
    title: 'Title 6',
  }, {
    title: 'Title 6',
  }, {
    title: 'Title 6',
  },{
    title: 'Title 6',
  }];
  export default defineComponent({
    setup() {
      return {
        data,
      };
    },
  });
  </script>