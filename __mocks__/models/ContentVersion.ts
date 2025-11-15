const mockContentVersion = {
  id: 'test-version-id',
  contentId: 'test-content-id',
  content: '<p>Test version</p>',
  versionNumber: 1,
  comment: 'Initial version',
  createdById: 'test-user-id',
  save: jest.fn(),
  update: jest.fn(),
  destroy: jest.fn()
};

export const ContentVersion = {
  create: jest.fn().mockResolvedValue(mockContentVersion),
  findByPk: jest.fn().mockResolvedValue(mockContentVersion),
  findAll: jest.fn().mockResolvedValue([mockContentVersion]),
  findOne: jest.fn().mockResolvedValue(mockContentVersion),
  build: jest.fn().mockReturnValue(mockContentVersion),
  count: jest.fn().mockResolvedValue(1)
};