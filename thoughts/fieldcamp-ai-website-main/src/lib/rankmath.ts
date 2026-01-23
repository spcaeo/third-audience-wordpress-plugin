import axios from 'axios';

export async function getRankMathMetadata(slug: string) {
  try {
    const response = await axios.get(`https://cms.fieldcamp.ai/wp-json/rankmath/v1/meta?slug=${slug}`);
    return response.data;
  } catch (error) {
    console.error("Error fetching Rank Math metadata:", error);
    return {
      title: '',
      description: '',
      imageUrl: '',
    }; // Return default values in case of error
  }
} 
