import { mount } from '@vue/test-utils'
import SelectContentModal from '@/Components/Category/SelectContentModal.vue'
import axios from 'axios'

jest.mock('axios')

describe('SelectContentModal', () => {
  it('renders correctly when closed', () => {
    const wrapper = mount(SelectContentModal, {
      props: {
        modelValue: false
      }
    })

    expect(wrapper.find('h3').exists()).toBe(false)
  })

  it('renders correctly when opened', async () => {
    const wrapper = mount(SelectContentModal, {
      props: {
        modelValue: true
      }
    })

    expect(wrapper.find('h3').text()).toBe('Select Content')
    expect(wrapper.find('input').exists()).toBe(true)
  })

  it('fetches contents when opened', async () => {
    const mockContents = [
      { id: 1, title: 'Test Content 1' },
      { id: 2, title: 'Test Content 2' }
    ]
    axios.get.mockResolvedValue({ data: mockContents })

    const wrapper = mount(SelectContentModal, {
      props: {
        modelValue: true
      }
    })

    await wrapper.vm.$nextTick()
    expect(axios.get).toHaveBeenCalledWith('/api/contents')
    expect(wrapper.vm.contents).toEqual(mockContents)
  })

  it('filters contents based on search query', async () => {
    const mockContents = [
      { id: 1, title: 'Apple' },
      { id: 2, title: 'Banana' },
      { id: 3, title: 'Orange' }
    ]
    axios.get.mockResolvedValue({ data: mockContents })

    const wrapper = mount(SelectContentModal, {
      props: {
        modelValue: true
      }
    })

    await wrapper.vm.$nextTick()
    await wrapper.setData({ searchQuery: 'app' })
    expect(wrapper.vm.filteredContents).toEqual([{ id: 1, title: 'Apple' }])
  })

  it('emits selected content when clicked', async () => {
    const mockContent = { id: 1, title: 'Test Content' }
    axios.get.mockResolvedValue({ data: [mockContent] })

    const wrapper = mount(SelectContentModal, {
      props: {
        modelValue: true
      }
    })

    await wrapper.vm.$nextTick()
    await wrapper.find('.cursor-pointer').trigger('click')
    expect(wrapper.emitted('selected')).toBeTruthy()
    expect(wrapper.emitted('selected')[0]).toEqual([mockContent])
  })
})