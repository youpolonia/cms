const mockContent = {
  id: 'test-content-id',
  title: 'Test Content',
  slug: 'test-content',
  content: '<p>Test</p>',
  status: 'draft',
  createdById: 'test-user-id',
  save: jest.fn(),
  update: jest.fn(),
  destroy: jest.fn()
};

export const Content = {
  create: jest.fn().mockResolvedValue(mockContent),
  findByPk: jest.fn().mockResolvedValue(mockContent),
  findAll: jest.fn().mockResolvedValue([mockContent]),
  findOne: jest.fn().mockResolvedValue(mockContent),
  build: jest.fn().mockReturnValue(mockContent)
};