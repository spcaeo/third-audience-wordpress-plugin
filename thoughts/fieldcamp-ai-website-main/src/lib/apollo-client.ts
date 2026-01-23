import { ApolloClient, InMemoryCache } from "@apollo/client";

export function getClient() {
  return new ApolloClient({
     //uri: "http://localhost/fieldcamp/graphql",
    uri: "https://cms.fieldcamp.ai/graphql",
    cache: new InMemoryCache(),
  });
}
 