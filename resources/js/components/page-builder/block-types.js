export const BlockTypes = {
  video: {
    label: "Video Embed",
    icon: "video",
    fields: [
      {
        key: "video_url",
        label: "Video URL",
        type: "url"
      },
      {
        key: "caption",
        label: "Caption",
        type: "richtext"  
      },
      {
        key: "autoplay", 
        label: "Autoplay",
        type: "checkbox",
        default: false
      }
    ]
  },
  card: {
    label: "Card",  
    icon: "cards",
    variants: ["default", "masonry"],
    fields: [
      {
        key: "title",
        label: "Card Title",
        type: "text"
      },
      {  
        key: "content",
        label: "Card Content",
        type: "richtext"
      },
      {
        key: "image",
        label: "Card Image", 
        type: "image-upload"
      }
    ]
  }
};