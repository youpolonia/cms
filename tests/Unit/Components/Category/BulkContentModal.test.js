import { mount } from '@vue/test-utils'
import BulkContentModal from '@/Components/Category/BulkContentModal.vue'

describe('BulkContentModal', () => {
  const currentContents = [
    { id: 1, title: 'Content 1' },
    { id: 2, title: 'Content 2' }
  ]

  it('renders correctly when closed', () => {
    const wrapper = mount(BulkContentModal, {
      props: {
        modelValue: false,
        currentContents
      }
    })

    expect(wrapper.find('h3').exists()).toBe(false)
  })

  it('renders correctly when opened', async () => {
    const wrapper = mount(BulkContentModal, {
      props: {
        modelValue: true,
        currentContents
      }
    })

    expect(wrapper.find('h3').text()).toBe('Bulk Content Operations')
    expect(wrapper.findAll('h4').length).toBe(2)
    expect(wrapper.findAll('select-content-stub').length).toBe(2)
  })

  it('emits add event with selected contents', async () => {
    const wrapper = mount(BulkContentModal, {
      props: {
        modelValue: true,
        currentContents
      }
    })

    await wrapper.setData({
      contentsToAdd: [{ id: 3, title: 'New Content' }]
    })
    await wrapper.find('.btn-primary').trigger('click')

    expect(wrapper.emitted('add')).toBeTruthy()
    expect(wrapper.emitted('add')[0]).toEqual([[{ id: 3, title: 'New Content' }]])
  })

  it('emits remove event with selected contents', async () => {
    const wrapper = mount(BulkContentModal, {
      props: {
        modelValue: true,
        currentContents
      }
    })

    await wrapper.setData({
      contentsToRemove: [{ id: 1, title: 'Content 1' }]
    })
    await wrapper.find('.btn-primary').trigger('click')

    expect(wrapper.emitted('remove')).toBeTruthy()
    expect(wrapper.emitted('remove')[0]).toEqual([[{ id: 1, title: 'Content 1' }]])
  })

  it('emits both add and remove events when both are selected', async () => {
    const wrapper = mount(BulkContentModal, {
      props: {
        modelValue: true,
        currentContents
      }
    })

    await wrapper.setData({
      contentsToAdd: [{ id: 3, title: 'New Content' }],
      contentsToRemove: [{ id: 1, title: 'Content 1' }]
    })
    await wrapper.find('.btn-primary').trigger('click')

    expect(wrapper.emitted('add')).toBeTruthy()
    expect(wrapper.emitted('remove')).toBeTruthy()
  })
})