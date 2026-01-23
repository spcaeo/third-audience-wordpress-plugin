
import { Badge } from "@/app/_components/ui/badge";  
import { Card, CardContent, CardFooter, CardHeader } from "@/app/_components/ui/card";
import { Button } from "@/app/_components/ui/button";
import Link from "next/link";
import { ArrowRight } from "lucide-react";

interface Template {
  id: string;
  title: string;
  description: string;
  category: string;
  useCase: string;
  tags: string[];
  featured?: boolean;
}

interface TemplateCardProps {
  template: Template;
}

export function TemplateCard({ template }: TemplateCardProps) {
  // Color scheme based on template category
  const getCardColors = (category: string) => {
    switch (category) {
      case "team-automation":
        return "bg-gradient-to-br from-purple-50 to-purple-100";
      case "reporting":
        return "bg-gradient-to-br from-blue-50 to-blue-100";
      case "customers":
        return "bg-gradient-to-br from-green-50 to-green-100";
      case "leads":
        return "bg-gradient-to-br from-orange-50 to-orange-100";
      case "invoices":
        return "bg-gradient-to-br from-pink-50 to-pink-100";
      default:
        return "bg-gradient-to-br from-gray-50 to-gray-100";
    }
  };

  return (
    <Card className="group hover:shadow-lg transition-all duration-300 border border-gray-200 bg-white rounded-2xl overflow-hidden">
      {/* Preview Area */}
      <div className={`h-48 ${getCardColors(template.category)} flex items-center justify-center`}>
        <div className="w-40 h-32 bg-white rounded-lg shadow-sm border border-gray-200 flex items-center justify-center">
          <div className="text-xs text-gray-400 font-medium">Preview</div>
        </div>
      </div>

      <CardHeader className="pb-3 pt-6 px-6">
        <div className="flex items-start justify-between mb-3">
          <h3 className="text-lg font-semibold text-gray-900 leading-tight">
            {template.title}
          </h3>
          {template.featured && (
            <Badge className="bg-orange-100 text-orange-700 border-0 rounded-full text-xs font-medium">
              Featured
            </Badge>
          )}
        </div>
        <p className="text-gray-600 text-sm leading-relaxed">
          {template.description}
        </p>
      </CardHeader>

      <CardFooter className="pt-0 pb-6 px-6 flex flex-col items-start space-y-4">
        <Link href={`/template/${template.id}`} className="w-full">
          <Button 
            className="w-full group/btn bg-blue-600 hover:bg-blue-700 text-white border-0 rounded-lg h-10 text-sm font-medium transition-all duration-200 flex items-center justify-center"
          >
            Check it out
            <ArrowRight className="w-4 h-4 ml-2 group-hover/btn:translate-x-0.5 transition-transform duration-200" />
          </Button>
        </Link>
      </CardFooter>
    </Card>
  );
}
